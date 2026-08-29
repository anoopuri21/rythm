import assert from 'node:assert/strict';
import test from 'node:test';
import { createTeamChangeProposal, decideTeamChange, evaluateCompletion, validateAssignment } from '../../automation/supervisor/agents.mjs';

const assignment = { task_id: 'PHASE-6A-CHUNK-4', accountable: 'Agent 0', primary: ['Agent 2', 'Agent 3'], reviewers: ['Agent 9', 'Agent 11'] };

test('valid assignment keeps Agent 0 accountable and independent reviewers', () => {
    assert.deepEqual(validateAssignment(assignment), { valid: true, errors: [] });
});

test('specialists cannot review their own work or replace Agent 0 accountability', () => {
    const result = validateAssignment({ ...assignment, accountable: 'Agent 2', reviewers: ['Agent 2'] });
    assert.equal(result.valid, false);
    assert.match(result.errors.join('\n'), /Agent 0 must remain accountable/);
    assert.match(result.errors.join('\n'), /reviewers must be independent/);
});

test('Agent 10 remains unavailable before deployment activation', () => {
    const result = validateAssignment({ ...assignment, primary: ['Agent 10'] });
    assert.match(result.errors.join('\n'), /Agent 10 is inactive/);
    assert.equal(validateAssignment({ ...assignment, primary: ['Agent 10'] }, { deploymentEnabled: true }).valid, true);
});

test('completion requires passed evidence and explicit Agent 0 acceptance', () => {
    const accepted = evaluateCompletion({ assignment, specialist_claims: [{ agent: 'Agent 2', status: 'done' }], gates: [{ name: 'QA', outcome: 'passed', evidence: 'report.md' }], decision: { actor: 'Agent 0', status: 'accepted', reason: 'Independent evidence passed.' } });
    assert.equal(accepted.complete, true);
    const selfApproved = evaluateCompletion({ assignment, specialist_claims: [{ agent: 'Agent 2', status: 'done' }], gates: [{ name: 'QA', outcome: 'passed', evidence: 'report.md' }], decision: { actor: 'Agent 2', status: 'accepted', reason: 'My work is done.' } });
    assert.equal(selfApproved.complete, false);
    assert.match(selfApproved.errors.join('\n'), /only Agent 0/);
});

test('failed or evidence-free gates prevent completion', () => {
    const result = evaluateCompletion({ assignment, specialist_claims: [{ agent: 'Agent 2' }], gates: [{ name: 'QA', outcome: 'failed', evidence: '' }], decision: { actor: 'Agent 0', status: 'accepted', reason: 'Premature.' } });
    assert.equal(result.complete, false);
    assert.match(result.errors.join('\n'), /every gate must pass with evidence/);
});

test('Supervisor may propose an audited team change but cannot self-approve it', () => {
    const proposal = createTeamChangeProposal({ change_type: 'reassign', target_agent: 'Agent 2', reason: 'Frontend capacity conflict', capability: 'Move responsive QA support to Agent 13', overlap: 'Agent 13 reviews accessibility only', rollback: 'Restore Agent 2 after current chunk' });
    assert.equal(proposal.status, 'proposed');
    assert.match(proposal.id, /^TEAM-[a-f0-9]{16}$/);
    assert.throws(() => decideTeamChange(proposal, { actor: 'Autonomous Supervisor', status: 'approved', reason: 'Self approved' }), /Only Agent 0/);
    const approved = decideTeamChange(proposal, { actor: 'Agent 0', status: 'approved', reason: 'Bounded reassignment is justified.' });
    assert.equal(approved.status, 'approved');
});

test('team proposals require reason, capability, overlap and rollback', () => {
    assert.throws(() => createTeamChangeProposal({ change_type: 'add', target_agent: 'Agent 16', reason: '', capability: 'New skill', overlap: 'None', rollback: 'Remove role' }), /reason is required/);
    assert.throws(() => createTeamChangeProposal({ change_type: 'remove', target_agent: 'Agent 0', reason: 'x', capability: 'x', overlap: 'x', rollback: 'x' }), /cannot be removed/);
});

test('Agent 0 cannot approve Agent 10 activation before explicit deployment', () => {
    const proposal = createTeamChangeProposal({ change_type: 'capability_change', target_agent: 'Agent 10', reason: 'Prepare deployment', capability: 'Deployment execution', overlap: 'None', rollback: 'Return inactive' });
    assert.throws(() => decideTeamChange(proposal, { actor: 'Agent 0', status: 'approved', reason: 'Too early' }), /cannot be activated/);
});
