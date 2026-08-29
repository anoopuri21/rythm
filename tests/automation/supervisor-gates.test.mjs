import assert from 'node:assert/strict';
import test from 'node:test';
import { aggregatePhaseGates, evaluateGateSet, reconcileTrackerStatus, requiredGates } from '../../automation/supervisor/gates.mjs';

const commit = 'a'.repeat(40);
const started = '2026-08-27T10:00:00.000Z';
const passedGate = (name, overrides = {}) => ({ name, outcome: 'passed', evidence: `tasks/${name}.md`, verified_at: '2026-08-27T11:00:00.000Z', commit, ...overrides });

test('gate profiles produce a deduplicated applicable gate set', () => {
    const gates = requiredGates({ profiles: ['php', 'migration'], phaseGate: true });
    for (const expected of ['diff_check', 'focused_tests', 'isolated_migration_cycle', 'independent_review', 'full_regression']) assert.ok(gates.includes(expected));
    assert.equal(new Set(gates).size, gates.length);
});

test('unknown gate profile is rejected', () => {
    assert.throws(() => requiredGates({ profiles: ['magic'] }), /Unknown gate profile/);
});

test('all required current-commit evidence passes', () => {
    const required = requiredGates({ profiles: ['automation'] });
    const result = evaluateGateSet({ required, gates: required.map((name) => passedGate(name)), currentCommit: commit, taskStartedAt: started, evidenceExists: () => true });
    assert.equal(result.passed, true);
});

test('working-tree digest can bind pre-commit evidence', () => {
    const required = ['automation_tests'];
    const gate = passedGate('automation_tests', { commit: 'b'.repeat(40), working_tree_digest: 'tree-123' });
    const result = evaluateGateSet({ required, gates: [gate], currentCommit: commit, workingTreeDigest: 'tree-123', taskStartedAt: started });
    assert.equal(result.passed, true);
});

test('missing, stale, failed and unbound evidence cannot pass', () => {
    const required = ['one', 'two', 'three', 'four'];
    const gates = [
        passedGate('two', { verified_at: '2026-08-27T09:00:00.000Z' }),
        passedGate('three', { outcome: 'failed' }),
        passedGate('four', { commit: 'b'.repeat(40) })
    ];
    const result = evaluateGateSet({ required, gates, currentCommit: commit, taskStartedAt: started });
    assert.equal(result.passed, false);
    assert.match(result.results.flatMap((item) => item.errors).join('\n'), /missing/);
    assert.match(result.results.flatMap((item) => item.errors).join('\n'), /predates task/);
    assert.match(result.results.flatMap((item) => item.errors).join('\n'), /outcome failed/);
    assert.match(result.results.flatMap((item) => item.errors).join('\n'), /not bound/);
});

test('tracker cannot become COMPLETE without gates and Agent 0 acceptance', () => {
    assert.equal(reconcileTrackerStatus({ requestedStatus: 'COMPLETE', gateEvaluation: { passed: false }, agent0Decision: null }).status, 'QA');
    assert.equal(reconcileTrackerStatus({ requestedStatus: 'COMPLETE', gateEvaluation: { passed: true }, agent0Decision: { actor: 'Agent 9', status: 'accepted', reason: 'QA passed' } }).status, 'QA');
    assert.equal(reconcileTrackerStatus({ requestedStatus: 'COMPLETE', gateEvaluation: { passed: true }, agent0Decision: { actor: 'Agent 0', status: 'accepted', reason: 'All evidence accepted.' } }).status, 'COMPLETE');
});

test('critical blocker overrides a requested completion', () => {
    const result = reconcileTrackerStatus({ requestedStatus: 'COMPLETE', gateEvaluation: { passed: true }, agent0Decision: { actor: 'Agent 0', status: 'accepted', reason: 'x' }, hasCriticalBlocker: true });
    assert.equal(result.status, 'BLOCKED');
});

test('phase aggregation never self-completes and reports blocked/incomplete chunks', () => {
    assert.equal(aggregatePhaseGates([{ id: '1', status: 'BLOCKED', gates_passed: false }]).status, 'BLOCKED');
    assert.match(aggregatePhaseGates([{ id: '1', status: 'COMPLETE', gates_passed: true }, { id: '2', status: 'QA', gates_passed: true }]).reason, /Incomplete chunks: 2/);
    const allPassed = aggregatePhaseGates([{ id: '1', status: 'COMPLETE', gates_passed: true }]);
    assert.equal(allPassed.status, 'QA');
    assert.equal(allPassed.complete, false);
});
