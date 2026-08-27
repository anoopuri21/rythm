export const RECOVERY_POLICIES = Object.freeze({
    read_only: { maxAttempts: 3, automaticRetry: true, reconciliation: false },
    disposable_restore: { maxAttempts: 3, automaticRetry: true, reconciliation: true },
    idempotent_write: { maxAttempts: 2, automaticRetry: true, reconciliation: true },
    non_idempotent_write: { maxAttempts: 2, automaticRetry: false, reconciliation: true },
    destructive: { maxAttempts: 1, automaticRetry: false, reconciliation: true },
    financial: { maxAttempts: 1, automaticRetry: false, reconciliation: true },
    credential: { maxAttempts: 1, automaticRetry: false, reconciliation: true },
    production: { maxAttempts: 1, automaticRetry: false, reconciliation: true }
});

const TERMINAL_CLASSES = new Set(['destructive', 'financial', 'credential', 'production']);
const OUTCOMES = new Set(['succeeded', 'failed', 'unknown']);
const RECONCILIATIONS = new Set(['not_run', 'completed', 'not_completed', 'indeterminate']);

export function recoveryPolicy(actionClass) {
    const policy = RECOVERY_POLICIES[actionClass];
    if (!policy) throw new Error(`Unknown action class '${actionClass}'`);
    return policy;
}

export function reconcileOutcome({ actionType, before = {}, after = {}, expected = {} }) {
    if (actionType === 'commit') {
        if (before.local_head && after.local_head && before.local_head !== after.local_head && after.working_tree_clean === true) {
            return { status: 'completed', evidence: `HEAD advanced to ${after.local_head} and the working tree is clean.` };
        }
        if (before.local_head === after.local_head) return { status: 'not_completed', evidence: 'HEAD did not advance.' };
        return { status: 'indeterminate', evidence: 'Commit post-state is incomplete or inconsistent.' };
    }
    if (actionType === 'push') {
        if (expected.commit && after.remote_head === expected.commit) return { status: 'completed', evidence: `Remote reached expected commit ${expected.commit}.` };
        if (before.remote_head && after.remote_head === before.remote_head) return { status: 'not_completed', evidence: 'Remote HEAD did not advance.' };
        return { status: 'indeterminate', evidence: 'Remote changed but does not match the expected commit.' };
    }
    if (actionType === 'persistent_write') {
        if (after.operation_id && expected.operation_id === after.operation_id && after.evidence_hash && expected.evidence_hash === after.evidence_hash) {
            return { status: 'completed', evidence: 'Durable operation identity and evidence hash match.' };
        }
        if (after.proven_absent === true && after.preconditions_unchanged === true) {
            return { status: 'not_completed', evidence: 'Post-state proves no write and unchanged retry preconditions.' };
        }
        return { status: 'indeterminate', evidence: 'Persistent write outcome lacks conclusive durable evidence.' };
    }
    if (actionType === 'disposable_restore') {
        if (after.runtime_ready === true && after.integrity_verified === true) return { status: 'completed', evidence: 'Disposable runtime exists and passed integrity verification.' };
        if (after.runtime_ready === false) return { status: 'not_completed', evidence: 'Disposable runtime remains unavailable.' };
        return { status: 'indeterminate', evidence: 'Runtime restoration was not integrity-verified.' };
    }
    throw new Error(`Unknown reconciliation action type '${actionType}'`);
}

export function decideRecovery({ actionClass, attempt, outcome, reconciliation = 'not_run', retrySafetyProven = false }) {
    const policy = recoveryPolicy(actionClass);
    if (!Number.isInteger(attempt) || attempt < 1) throw new Error('attempt must be a positive integer');
    if (!OUTCOMES.has(outcome)) throw new Error(`Invalid outcome '${outcome}'`);
    if (!RECONCILIATIONS.has(reconciliation)) throw new Error(`Invalid reconciliation '${reconciliation}'`);

    if (outcome === 'succeeded' || reconciliation === 'completed') {
        return { decision: 'accept_completed', mayExecute: false, reason: 'The action is verified complete; duplicate execution is forbidden.' };
    }
    if (TERMINAL_CLASSES.has(actionClass)) {
        return { decision: 'human_gate', mayExecute: false, reason: `${actionClass} actions never receive an automatic retry.` };
    }
    if (outcome === 'unknown' && reconciliation === 'not_run') {
        return { decision: 'reconcile', mayExecute: false, reason: 'Unknown outcome must be inspected before retry or failure classification.' };
    }
    if (reconciliation === 'indeterminate') {
        return { decision: 'block', mayExecute: false, reason: 'Post-state is indeterminate; retry could duplicate effects.' };
    }
    if (attempt >= policy.maxAttempts) {
        return { decision: 'block', mayExecute: false, reason: `Retry budget exhausted at ${attempt}/${policy.maxAttempts}.` };
    }
    if (actionClass === 'non_idempotent_write') {
        if (reconciliation !== 'not_completed' || retrySafetyProven !== true) {
            return { decision: 'block', mayExecute: false, reason: 'Non-idempotent write requires proof of absence and retry safety.' };
        }
        return { decision: 'retry_once', mayExecute: true, reason: 'Post-state proves no write; one bounded retry is permitted.' };
    }
    if (actionClass === 'idempotent_write' && policy.reconciliation && reconciliation !== 'not_completed' && outcome !== 'failed') {
        return { decision: 'reconcile', mayExecute: false, reason: 'Write outcome requires post-state reconciliation.' };
    }
    if (actionClass === 'disposable_restore' && outcome === 'unknown' && reconciliation !== 'not_completed') {
        return { decision: 'reconcile', mayExecute: false, reason: 'Restored runtime must be integrity-checked before another attempt.' };
    }
    return { decision: 'retry', mayExecute: policy.automaticRetry, reason: `Bounded retry ${attempt + 1}/${policy.maxAttempts} is permitted.` };
}
