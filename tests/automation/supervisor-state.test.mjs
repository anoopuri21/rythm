import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { mkdtempSync, readFileSync, statSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import test from 'node:test';
import { checkpointState, readState, validateState, writeStateAtomic } from '../../automation/supervisor/state.mjs';

const root = path.resolve(import.meta.dirname, '../..');
const stateFile = path.join(root, 'tasks', 'autonomous-supervisor-state.json');
const load = () => JSON.parse(readFileSync(stateFile, 'utf8'));

test('committed supervisor state is valid and deployment remains disabled', () => {
    const state = load();
    const result = validateState(state);
    assert.deepEqual(result.errors, []);
    assert.equal(result.valid, true);
    assert.equal(state.git.branch, 'rhythm-uat');
    assert.equal(state.authorization.deployment_enabled, false);
});

test('validator rejects missing fields, unsafe branch and deployment activation', () => {
    const state = load();
    delete state.next_action;
    state.git.branch = 'main';
    state.authorization.deployment_enabled = true;
    const result = validateState(state);
    assert.equal(result.valid, false);
    assert.match(result.errors.join('\n'), /next_action is required/);
    assert.match(result.errors.join('\n'), /git.branch must equal rhythm-uat/);
    assert.match(result.errors.join('\n'), /deployment_enabled must remain false/);
});

test('validator rejects secret-bearing fields and recognizable private material', () => {
    const withField = load();
    withField.next_action.api_key = 'not-even-a-real-key';
    assert.match(validateState(withField).errors.join('\n'), /forbidden secret-bearing field/);

    const withMaterial = load();
    withMaterial.next_action.description = '-----BEGIN PRIVATE KEY-----';
    assert.match(validateState(withMaterial).errors.join('\n'), /secret material/);
});

test('checkpoint identity is deterministic for the same state and timestamp', () => {
    const state = load();
    const timestamp = '2026-08-27T10:30:00.000Z';
    const first = checkpointState(state, timestamp);
    const second = checkpointState(state, timestamp);
    assert.equal(first.checkpoint.id, second.checkpoint.id);
    assert.match(first.checkpoint.id, /^[a-f0-9]{64}$/);
    assert.equal(first.checkpoint.created_at, timestamp);
});

test('atomic writer replaces valid state and refuses invalid state without damage', () => {
    const directory = mkdtempSync(path.join(tmpdir(), 'rythme-supervisor-'));
    const target = path.join(directory, 'state.json');
    const valid = checkpointState(load(), '2026-08-27T10:31:00.000Z');
    writeStateAtomic(target, valid);
    assert.deepEqual(readState(target), valid);
    assert.equal(statSync(target).mode & 0o777, 0o600);

    const original = readFileSync(target, 'utf8');
    const invalid = structuredClone(valid);
    invalid.lifecycle = 'pretend-complete';
    assert.throws(() => writeStateAtomic(target, invalid), /Invalid supervisor state/);
    assert.equal(readFileSync(target, 'utf8'), original);
});

test('readState rejects malformed JSON and CLI reports compact status', () => {
    const directory = mkdtempSync(path.join(tmpdir(), 'rythme-supervisor-invalid-'));
    const malformed = path.join(directory, 'state.json');
    writeFileSync(malformed, '{nope', 'utf8');
    assert.throws(() => readState(malformed));

    const output = execFileSync(process.execPath, ['automation/supervisor-state.mjs', 'status'], { cwd: root, encoding: 'utf8' });
    const status = JSON.parse(output);
    assert.equal(status.lifecycle, 'executing');
    assert.equal(status.phase, '10');
    assert.equal(status.next_action, 'PHASE-1-AND-HOMEPAGE-RUNTIME-QA');
});

test('published JSON schema is parseable and identifies state version one', () => {
    const schema = JSON.parse(readFileSync(path.join(root, 'automation', 'supervisor', 'state-schema.json'), 'utf8'));
    assert.equal(schema.properties.schema_version.const, 1);
    assert.equal(schema.additionalProperties, false);
});
