import assert from 'node:assert/strict';
import test from 'node:test';
import { validateAssignment, createTeamChangeProposal, decideTeamChange } from '../../automation/supervisor/agents.mjs';
import { evaluateGateSet, reconcileTrackerStatus } from '../../automation/supervisor/gates.mjs';
import { bootstrapDecision, reconcileCheckpoint } from '../../automation/supervisor/lifecycle.mjs';
import { chooseNextTask } from '../../automation/supervisor/planner.mjs';
import { decideRecovery, reconcileOutcome } from '../../automation/supervisor/recovery.mjs';
import { checkpointState, validateState } from '../../automation/supervisor/state.mjs';
import { readFileSync } from 'node:fs';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '../..');
const state = () => JSON.parse(readFileSync(path.join(root, 'tasks', 'autonomous-supervisor-state.json'), 'utf8'));

test('Arena timeout after completed commit is accepted without rerun', () => {
    const outcome = reconcileOutcome({ actionType: 'commit', before: { local_head: 'a' }, after: { local_head: 'b', working_tree_clean: true } });
    const decision = decideRecovery({ actionClass: 'idempotent_write', attempt: 1, outcome: 'unknown', reconciliation: outcome.status });
    assert.equal(decision.decision, 'accept_completed');
    assert.equal(decision.mayExecute, false);
});

test('failed push retries only after remote proves no advance', () => {
    const outcome = reconcileOutcome({ actionType: 'push', before: { remote_head: 'a' }, after: { remote_head: 'a' }, expected: { commit: 'b' } });
    assert.equal(outcome.status, 'not_completed');
    const decision = decideRecovery({ actionClass: 'idempotent_write', attempt: 1, outcome: 'unknown', reconciliation: outcome.status });
    assert.equal(decision.decision, 'retry');
    assert.equal(decision.mayExecute, true);
});

test('interrupted import with uncertain durable state is blocked', () => {
    const outcome = reconcileOutcome({ actionType: 'persistent_write', after: {}, expected: { operation_id: 'phase6a-batch2', evidence_hash: 'expected' } });
    const decision = decideRecovery({ actionClass: 'non_idempotent_write', attempt: 1, outcome: 'unknown', reconciliation: outcome.status });
    assert.equal(decision.decision, 'block');
    assert.equal(decision.mayExecute, false);
});

test('missing disposable runtime can retry but unverified restoration cannot pass', () => {
    const missing = reconcileOutcome({ actionType: 'disposable_restore', after: { runtime_ready: false } });
    assert.equal(decideRecovery({ actionClass: 'disposable_restore', attempt: 1, outcome: 'unknown', reconciliation: missing.status }).mayExecute, true);
    const unverified = reconcileOutcome({ actionType: 'disposable_restore', after: { runtime_ready: true, integrity_verified: false } });
    assert.equal(decideRecovery({ actionClass: 'disposable_restore', attempt: 2, outcome: 'unknown', reconciliation: unverified.status }).decision, 'block');
});

test('secret injection is rejected before checkpoint persistence', () => {
    const unsafe = state();
    unsafe.next_action.private_key = '-----BEGIN PRIVATE KEY-----';
    assert.equal(validateState(unsafe).valid, false);
    assert.throws(() => checkpointState(unsafe), /forbidden secret-bearing field|secret material/);
});

test('wrong branch blocks both resume and task planning', () => {
    const current = state();
    const audit = { safe_for_planning: false, findings: [{ severity: 'critical', code: 'WRONG_BRANCH' }], git: { branch: 'main', local_head: 'a', remote_head: 'a', remote_available: true, working_tree_clean: true } };
    assert.equal(reconcileCheckpoint(current, audit).status, 'blocked');
    assert.equal(chooseNextTask({ state: current, audit, trackerMarkdown: '', expansionMarkdown: '' }).status, 'blocked');
});

test('dirty tree prevents active bootstrap from executing', () => {
    const current = { ...state(), lifecycle: 'executing' };
    const audit = { safe_for_planning: true, git: { branch: 'rhythm-uat', local_head: current.git.local_head, remote_head: current.git.remote_head, remote_available: true, working_tree_clean: false } };
    const result = bootstrapDecision({ state: current, audit, plan: { status: 'ready', task: { id: 'X' } }, config: { enabled: true } });
    assert.equal(result.allowed, false);
    assert.equal(result.action, 'inspect_interrupted_work');
});

test('failed evidence and specialist self-approval cannot complete a task', () => {
    const gates = evaluateGateSet({ required: ['qa'], gates: [{ name: 'qa', outcome: 'failed', evidence: 'x', verified_at: '2026-08-27T12:00:00Z', commit: 'a' }], currentCommit: 'a', taskStartedAt: '2026-08-27T11:00:00Z' });
    const result = reconcileTrackerStatus({ requestedStatus: 'COMPLETE', gateEvaluation: gates, agent0Decision: { actor: 'Agent 9', status: 'accepted', reason: 'self approval' } });
    assert.equal(result.status, 'QA');
});

test('Phase 18 bypass through assignment and team change is rejected', () => {
    const assignment = validateAssignment({ task_id: 'PHASE-18', accountable: 'Agent 0', primary: ['Agent 10'], reviewers: ['Agent 9'] });
    assert.equal(assignment.valid, false);
    const proposal = createTeamChangeProposal({ change_type: 'capability_change', target_agent: 'Agent 10', reason: 'attempt', capability: 'deploy', overlap: 'none', rollback: 'disable' });
    assert.throws(() => decideTeamChange(proposal, { actor: 'Agent 0', status: 'approved', reason: 'attempt' }), /cannot be activated/);
});

test('planner completion boundary excludes inactive Phase 18', () => {
    const current = { ...state(), lifecycle: 'executing' };
    const tracker = '| 6A | Team | Catalogue | COMPLETE | gate |\n| 8 | Team | Payment | COMPLETE | gate |\n| 17 | Team | Sign-off | COMPLETE | gate |\n| 18 | Agent 10 | Deploy | INACTIVE | gate |';
    const result = chooseNextTask({ state: current, audit: { safe_for_planning: true, findings: [] }, trackerMarkdown: tracker, expansionMarkdown: '' });
    assert.equal(result.status, 'complete');
});
