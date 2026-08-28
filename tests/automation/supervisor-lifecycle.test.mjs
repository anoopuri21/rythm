import assert from 'node:assert/strict';
import test from 'node:test';
import { bootstrapDecision, reconcileCheckpoint, supervisorStatus } from '../../automation/supervisor/lifecycle.mjs';

const state = { lifecycle: 'building', git: { branch: 'rhythm-uat', local_head: 'a', remote_head: 'a' } };
const audit = (changes = {}) => ({ safe_for_planning: true, git: { branch: 'rhythm-uat', local_head: 'a', remote_head: 'a', remote_available: true, working_tree_clean: true, ...changes } });
const plan = { status: 'ready', task: { id: 'AS-BUILD-6-RUNBOOK' } };

test('matching clean checkpoint continues safely', () => {
    assert.deepEqual(reconcileCheckpoint(state, audit()), { status: 'current', action: 'continue_checkpointed_action', reason: 'Repository matches the recorded checkpoint baseline.' });
});

test('clean local and remote advance is recognized, not treated as failure', () => {
    const result = reconcileCheckpoint(state, audit({ local_head: 'b', remote_head: 'b' }));
    assert.equal(result.status, 'advanced');
    assert.equal(result.action, 'accept_verified_advance');
});

test('dirty tree and local remote divergence require inspection', () => {
    assert.equal(reconcileCheckpoint(state, audit({ working_tree_clean: false })).action, 'inspect_interrupted_work');
    assert.equal(reconcileCheckpoint(state, audit({ local_head: 'b', remote_head: 'a' })).action, 'reconcile_local_remote_divergence');
});

test('wrong branch and unavailable remote block resume', () => {
    assert.equal(reconcileCheckpoint(state, audit({ branch: 'main' })).status, 'blocked');
    assert.equal(reconcileCheckpoint(state, audit({ remote_available: false })).action, 'restore_remote_visibility');
});

test('status exposes activation, reconciliation and next plan', () => {
    const result = supervisorStatus({ state, audit: audit(), plan, config: { enabled: false } });
    assert.equal(result.activation, 'not_active');
    assert.equal(result.reconciliation.status, 'current');
    assert.equal(result.next.task.id, 'AS-BUILD-6-RUNBOOK');
});

test('bootstrap stays write-disabled throughout build', () => {
    const result = bootstrapDecision({ state, audit: audit(), plan, config: { enabled: false } });
    assert.equal(result.allowed, false);
    assert.equal(result.status, 'building');
});

test('active lifecycle bootstraps only when audit and checkpoint are safe', () => {
    const activeState = { ...state, lifecycle: 'executing' };
    assert.equal(bootstrapDecision({ state: activeState, audit: audit(), plan, config: { enabled: true } }).allowed, true);
    assert.equal(bootstrapDecision({ state: activeState, audit: { ...audit(), safe_for_planning: false }, plan, config: { enabled: true } }).allowed, false);
    assert.equal(bootstrapDecision({ state: activeState, audit: audit({ working_tree_clean: false }), plan, config: { enabled: true } }).allowed, false);
});
