import assert from 'node:assert/strict';
import test from 'node:test';
import { decideRecovery, reconcileOutcome, recoveryPolicy } from '../../automation/supervisor/recovery.mjs';

test('risk policies cap reversible retries and disable dangerous automatic retries', () => {
    assert.deepEqual(recoveryPolicy('read_only'), { maxAttempts: 3, automaticRetry: true, reconciliation: false });
    for (const kind of ['destructive', 'financial', 'credential', 'production']) {
        assert.equal(recoveryPolicy(kind).automaticRetry, false);
        assert.equal(recoveryPolicy(kind).maxAttempts, 1);
    }
});

test('unknown outcome always reconciles before a reversible retry', () => {
    assert.equal(decideRecovery({ actionClass: 'read_only', attempt: 1, outcome: 'unknown' }).decision, 'reconcile');
    assert.equal(decideRecovery({ actionClass: 'idempotent_write', attempt: 1, outcome: 'unknown' }).mayExecute, false);
});

test('verified completion is accepted without duplicate execution', () => {
    const result = decideRecovery({ actionClass: 'idempotent_write', attempt: 1, outcome: 'unknown', reconciliation: 'completed' });
    assert.equal(result.decision, 'accept_completed');
    assert.equal(result.mayExecute, false);
});

test('read-only failures retry within budget and block after exhaustion', () => {
    assert.equal(decideRecovery({ actionClass: 'read_only', attempt: 1, outcome: 'failed' }).decision, 'retry');
    assert.equal(decideRecovery({ actionClass: 'read_only', attempt: 3, outcome: 'failed' }).decision, 'block');
});

test('non-idempotent write retries only after absence and safety proof', () => {
    const unsafe = decideRecovery({ actionClass: 'non_idempotent_write', attempt: 1, outcome: 'unknown', reconciliation: 'not_completed' });
    assert.equal(unsafe.decision, 'block');
    const safe = decideRecovery({ actionClass: 'non_idempotent_write', attempt: 1, outcome: 'unknown', reconciliation: 'not_completed', retrySafetyProven: true });
    assert.equal(safe.decision, 'retry_once');
    assert.equal(safe.mayExecute, true);
});

test('dangerous classes always become human gates', () => {
    for (const kind of ['destructive', 'financial', 'credential', 'production']) {
        const result = decideRecovery({ actionClass: kind, attempt: 1, outcome: 'failed' });
        assert.equal(result.decision, 'human_gate');
        assert.equal(result.mayExecute, false);
    }
});

test('indeterminate post-state blocks instead of retrying', () => {
    const result = decideRecovery({ actionClass: 'disposable_restore', attempt: 1, outcome: 'unknown', reconciliation: 'indeterminate' });
    assert.equal(result.decision, 'block');
});

test('commit reconciliation distinguishes completed and absent outcomes', () => {
    assert.equal(reconcileOutcome({ actionType: 'commit', before: { local_head: 'a' }, after: { local_head: 'b', working_tree_clean: true } }).status, 'completed');
    assert.equal(reconcileOutcome({ actionType: 'commit', before: { local_head: 'a' }, after: { local_head: 'a', working_tree_clean: false } }).status, 'not_completed');
});

test('push reconciliation requires the exact expected remote commit', () => {
    assert.equal(reconcileOutcome({ actionType: 'push', before: { remote_head: 'a' }, after: { remote_head: 'b' }, expected: { commit: 'b' } }).status, 'completed');
    assert.equal(reconcileOutcome({ actionType: 'push', before: { remote_head: 'a' }, after: { remote_head: 'c' }, expected: { commit: 'b' } }).status, 'indeterminate');
});

test('persistent write reconciliation needs durable identity or proof of absence', () => {
    assert.equal(reconcileOutcome({ actionType: 'persistent_write', after: { operation_id: 'batch-1', evidence_hash: 'hash' }, expected: { operation_id: 'batch-1', evidence_hash: 'hash' } }).status, 'completed');
    assert.equal(reconcileOutcome({ actionType: 'persistent_write', after: { proven_absent: true, preconditions_unchanged: true }, expected: {} }).status, 'not_completed');
    assert.equal(reconcileOutcome({ actionType: 'persistent_write', after: {}, expected: {} }).status, 'indeterminate');
});

test('disposable restore must pass integrity verification', () => {
    assert.equal(reconcileOutcome({ actionType: 'disposable_restore', after: { runtime_ready: true, integrity_verified: true } }).status, 'completed');
    assert.equal(reconcileOutcome({ actionType: 'disposable_restore', after: { runtime_ready: true, integrity_verified: false } }).status, 'indeterminate');
});
