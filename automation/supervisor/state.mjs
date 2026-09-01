import { createHash } from 'node:crypto';
import { closeSync, fsyncSync, openSync, readFileSync, renameSync, unlinkSync, writeFileSync } from 'node:fs';
import path from 'node:path';

export const LIFECYCLES = new Set(['inactive', 'planning', 'building', 'executing', 'recovering', 'paused', 'blocked', 'complete']);
export const ACTION_CLASSES = new Set(['read_only', 'disposable_restore', 'idempotent_write', 'non_idempotent_write', 'destructive', 'financial', 'credential', 'production']);
export const OUTCOMES = new Set(['passed', 'failed', 'blocked', 'not_applicable']);

const REQUIRED = ['schema_version', 'project', 'lifecycle', 'authorization', 'git', 'delivery', 'execution', 'agents', 'checkpoint', 'decisions', 'risks', 'blockers', 'human_gates', 'gate_results', 'next_action'];
const FORBIDDEN_KEY = /(^|_)(password|secret|credential|api_?key|access_?token|private_?key)($|_)/i;
const SECRET_VALUE = /(-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----|gh[pousr]_[A-Za-z0-9_]{20,}|sk_(?:live|test)_[A-Za-z0-9]{16,})/;

const isObject = (value) => value !== null && typeof value === 'object' && !Array.isArray(value);
const requireString = (errors, object, key, location) => {
    if (typeof object?.[key] !== 'string' || object[key].trim() === '') errors.push(`${location}.${key} must be a non-empty string`);
};
const requireBoolean = (errors, object, key, location) => {
    if (typeof object?.[key] !== 'boolean') errors.push(`${location}.${key} must be boolean`);
};

function inspectSecrets(value, location, errors) {
    if (Array.isArray(value)) {
        value.forEach((item, index) => inspectSecrets(item, `${location}[${index}]`, errors));
        return;
    }
    if (!isObject(value)) {
        if (typeof value === 'string' && SECRET_VALUE.test(value)) errors.push(`${location} appears to contain secret material`);
        return;
    }
    for (const [key, child] of Object.entries(value)) {
        if (FORBIDDEN_KEY.test(key)) errors.push(`${location}.${key} is a forbidden secret-bearing field`);
        inspectSecrets(child, `${location}.${key}`, errors);
    }
}

export function validateState(state) {
    const errors = [];
    if (!isObject(state)) return { valid: false, errors: ['state must be an object'] };

    for (const key of REQUIRED) if (!(key in state)) errors.push(`${key} is required`);
    for (const key of Object.keys(state)) if (!REQUIRED.includes(key)) errors.push(`${key} is not allowed at the state root`);
    if (state.schema_version !== 1) errors.push('schema_version must equal 1');
    if (!LIFECYCLES.has(state.lifecycle)) errors.push('lifecycle is invalid');

    requireString(errors, state.project, 'name', 'project');
    requireString(errors, state.project, 'repository', 'project');
    requireString(errors, state.authorization, 'through_phase', 'authorization');
    requireBoolean(errors, state.authorization, 'deployment_enabled', 'authorization');
    if (state.authorization?.deployment_enabled !== false) errors.push('authorization.deployment_enabled must remain false before explicit Phase 18 activation');

    requireString(errors, state.git, 'branch', 'git');
    requireString(errors, state.git, 'local_head', 'git');
    requireString(errors, state.git, 'remote_head', 'git');
    requireBoolean(errors, state.git, 'working_tree_clean', 'git');
    if (state.git?.branch !== 'rhythm-uat') errors.push('git.branch must equal rhythm-uat');
    for (const key of ['local_head', 'remote_head']) {
        if (typeof state.git?.[key] === 'string' && !/^[a-f0-9]{40}$/.test(state.git[key])) errors.push(`git.${key} must be a full commit SHA`);
    }

    requireString(errors, state.delivery, 'phase', 'delivery');
    requireString(errors, state.delivery, 'chunk', 'delivery');
    requireString(errors, state.delivery, 'task', 'delivery');
    requireString(errors, state.delivery, 'status', 'delivery');

    if (!ACTION_CLASSES.has(state.execution?.action_class)) errors.push('execution.action_class is invalid');
    if (!Number.isInteger(state.execution?.attempt) || state.execution.attempt < 0) errors.push('execution.attempt must be a non-negative integer');
    if (!Number.isInteger(state.execution?.max_attempts) || state.execution.max_attempts < 0 || state.execution.max_attempts > 3) errors.push('execution.max_attempts must be an integer from 0 to 3');
    requireString(errors, state.execution, 'last_verified_action', 'execution');

    requireString(errors, state.agents, 'accountable', 'agents');
    if (!Array.isArray(state.agents?.primary) || !Array.isArray(state.agents?.reviewers)) errors.push('agents.primary and agents.reviewers must be arrays');

    requireString(errors, state.checkpoint, 'id', 'checkpoint');
    requireString(errors, state.checkpoint, 'created_at', 'checkpoint');
    if (typeof state.checkpoint?.id === 'string' && !/^[a-f0-9]{64}$/.test(state.checkpoint.id)) errors.push('checkpoint.id must be a SHA-256 hex digest');
    if (typeof state.checkpoint?.created_at === 'string' && Number.isNaN(Date.parse(state.checkpoint.created_at))) errors.push('checkpoint.created_at must be an ISO-compatible timestamp');

    for (const key of ['decisions', 'risks', 'blockers', 'human_gates', 'gate_results']) {
        if (!Array.isArray(state[key])) errors.push(`${key} must be an array`);
    }
    if (Array.isArray(state.gate_results)) {
        state.gate_results.forEach((gate, index) => {
            requireString(errors, gate, 'name', `gate_results[${index}]`);
            if (!OUTCOMES.has(gate?.outcome)) errors.push(`gate_results[${index}].outcome is invalid`);
            requireString(errors, gate, 'evidence', `gate_results[${index}]`);
            requireString(errors, gate, 'verified_at', `gate_results[${index}]`);
        });
    }

    requireString(errors, state.next_action, 'id', 'next_action');
    requireString(errors, state.next_action, 'description', 'next_action');
    requireBoolean(errors, state.next_action, 'requires_human', 'next_action');
    inspectSecrets(state, 'state', errors);

    return { valid: errors.length === 0, errors };
}

function canonicalize(value) {
    if (Array.isArray(value)) return value.map(canonicalize);
    if (!isObject(value)) return value;
    return Object.fromEntries(Object.keys(value).sort().map((key) => [key, canonicalize(value[key])]));
}

export function checkpointState(state, createdAt = new Date().toISOString()) {
    const next = structuredClone(state);
    next.checkpoint = { id: '0'.repeat(64), created_at: createdAt };
    const digest = createHash('sha256').update(JSON.stringify(canonicalize(next))).digest('hex');
    next.checkpoint.id = digest;
    const result = validateState(next);
    if (!result.valid) throw new Error(`Invalid supervisor state:\n- ${result.errors.join('\n- ')}`);
    return next;
}

export function readState(file) {
    const state = JSON.parse(readFileSync(file, 'utf8'));
    const result = validateState(state);
    if (!result.valid) throw new Error(`Invalid supervisor state:\n- ${result.errors.join('\n- ')}`);
    return state;
}

export function writeStateAtomic(file, state) {
    const result = validateState(state);
    if (!result.valid) throw new Error(`Invalid supervisor state:\n- ${result.errors.join('\n- ')}`);
    const directory = path.dirname(file);
    const temporary = path.join(directory, `.${path.basename(file)}.${process.pid}.${Date.now()}.tmp`);
    let descriptor;
    try {
        descriptor = openSync(temporary, 'wx', 0o600);
        writeFileSync(descriptor, `${JSON.stringify(state, null, 2)}\n`, 'utf8');
        fsyncSync(descriptor);
        closeSync(descriptor);
        descriptor = undefined;
        renameSync(temporary, file);
        try {
            const directoryDescriptor = openSync(directory, 'r');
            fsyncSync(directoryDescriptor);
            closeSync(directoryDescriptor);
        } catch {
            // Some filesystems do not support directory fsync; atomic rename still applies.
        }
    } catch (error) {
        if (descriptor !== undefined) closeSync(descriptor);
        try { unlinkSync(temporary); } catch {}
        throw error;
    }
}
