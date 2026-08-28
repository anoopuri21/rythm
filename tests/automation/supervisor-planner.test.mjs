import assert from 'node:assert/strict';
import path from 'node:path';
import test from 'node:test';
import { auditProject } from '../../automation/supervisor/auditor.mjs';
import { chooseNextTask, parseExpansionNext, parsePhaseStatuses } from '../../automation/supervisor/planner.mjs';
import { readState } from '../../automation/supervisor/state.mjs';

const root = path.resolve(import.meta.dirname, '../..');
const safeAudit = { safe_for_planning: true, findings: [] };
const executingState = { lifecycle: 'executing', next_action: { id: 'PROJECT-NEXT' } };

test('read-only auditor verifies branch, authorities and disabled deployment', () => {
    const audit = auditProject(root);
    assert.equal(audit.mode, 'read-only');
    assert.equal(audit.git.branch, 'rhythm-uat');
    assert.equal(audit.state.lifecycle, 'executing');
    assert.equal(audit.authority_sources.every((source) => source.exists && /^[a-f0-9]{64}$/.test(source.sha256)), true);
    assert.equal(audit.findings.some((finding) => finding.code === 'PREMATURE_AUTOMATION_ENABLE'), false);
    assert.equal(audit.findings.some((finding) => finding.code === 'DEPLOYMENT_ENABLED'), false);
});

test('building lifecycle selects exact checkpointed build action', () => {
    const committed = readState(path.join(root, 'tasks', 'autonomous-supervisor-state.json'));
    const state = { ...committed, lifecycle: 'building', next_action: { id: 'AS-BUILD-8-ACTIVATION' } };
    const result = chooseNextTask({ state, audit: safeAudit, trackerMarkdown: '', expansionMarkdown: '' });
    assert.equal(result.status, 'ready');
    assert.equal(result.source, 'supervisor-build-state');
    assert.equal(result.task.id, 'AS-BUILD-8-ACTIVATION');
});

test('critical audit finding blocks all planning', () => {
    const result = chooseNextTask({
        state: executingState,
        audit: { safe_for_planning: false, findings: [{ severity: 'critical', code: 'WRONG_BRANCH' }] },
        trackerMarkdown: '',
        expansionMarkdown: ''
    });
    assert.equal(result.status, 'blocked');
    assert.equal(result.findings[0].code, 'WRONG_BRANCH');
});

test('phase parser reads canonical statuses and ignores unrelated tables', () => {
    const phases = parsePhaseStatuses([
        '| Phase | Owner(s) | Scope | Status | Completion gate |',
        '| 7 | Agent 0 | Governance | COMPLETE | accepted |',
        '| 6A | Agent 5 | Expansion | IN PROGRESS | pending |',
        '| 8 | Agent 12 | Finance | PENDING | tests |'
    ].join('\n'));
    assert.deepEqual(phases, [{ phase: '7', status: 'COMPLETE' }, { phase: '6A', status: 'IN PROGRESS' }, { phase: '8', status: 'PENDING' }]);
});

test('open phase 6A takes priority and selects its explicit next chunk', () => {
    const tracker = '| 6A | Team | Catalogue | IN PROGRESS | gate |\n| 8 | Team | Finance | PENDING | gate |';
    const expansion = '- **Chunk 3 — QA:** owner evidence\n- **Chunk 4 — NEXT:** Build Homepage category discovery.';
    assert.deepEqual(parseExpansionNext(expansion), { chunk: '4', description: 'Build Homepage category discovery.' });
    const result = chooseNextTask({ state: executingState, audit: safeAudit, trackerMarkdown: tracker, expansionMarkdown: expansion });
    assert.equal(result.task.id, 'PHASE-6A-CHUNK-4');
    assert.equal(result.source, 'phase-6A-contract');
});

test('planner advances to first incomplete authorized phase after phase 6A', () => {
    const tracker = [
        '| 6A | Team | Catalogue | COMPLETE | gate |',
        '| 8 | Team | Finance | COMPLETE | gate |',
        '| 9 | Team | Notifications | PENDING | gate |',
        '| 10 | Team | Fulfillment | PENDING | gate |',
        '| 18 | Agent 10 | Deploy | INACTIVE | gate |'
    ].join('\n');
    const result = chooseNextTask({ state: executingState, audit: safeAudit, trackerMarkdown: tracker, expansionMarkdown: '' });
    assert.equal(result.status, 'ready');
    assert.equal(result.task.id, 'PHASE-9');
});

test('planner never selects phase 18 and reports authorized completion', () => {
    const tracker = '| 6A | Team | Catalogue | COMPLETE | gate |\n| 8 | Team | Finance | COMPLETE | gate |\n| 17 | Team | Review | COMPLETE | gate |\n| 18 | Agent 10 | Deploy | INACTIVE | gate |';
    const result = chooseNextTask({ state: executingState, audit: safeAudit, trackerMarkdown: tracker, expansionMarkdown: '' });
    assert.equal(result.status, 'complete');
});
