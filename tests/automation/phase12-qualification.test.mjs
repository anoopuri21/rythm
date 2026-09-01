import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const pack = read('tasks/PHASE_12_QUALIFICATION_EVIDENCE.md');
const tracker = read('tasks/MASTER_PROJECT_TRACKER.md');
const plan = read('tasks/AUTO_MODE_PHASE_12_PLAN.md');
const state = JSON.parse(read('tasks/autonomous-supervisor-state.json'));

await test('Phase 12 completion is recorded honestly with the standing legal gate', () => {
    assert.match(tracker, /\| 12 \|[^\n]+\| COMPLETE \|/);
    assert.match(tracker, /Accepted 1 Sep 2026: owner-reported 396 PHP tests \/ 1,704 assertions \/ 0 failures/);
    assert.match(tracker, /AS-H011[\s\S]{0,200}standing pre-launch gate/);
    assert.match(plan, /### Chunk 4 — independent Phase 12 qualification[\s\S]*?\*\*Status:\*\* COMPLETE/);
});

await test('the redacted evidence pack records every Arena-side gate and contract', () => {
    for (const required of [
        'security-phase12-boundaries.test.mjs',
        'security-phase12-config.test.mjs',
        'privacy-phase12-chunk3.test.mjs',
        'Agent 0 independent review record',
        'not a production-readiness claim',
    ]) {
        assert.ok(pack.includes(required), `evidence pack missing: ${required}`);
    }
});

await test('the evidence pack binds the remaining owner-side and legal human gates', () => {
    for (const required of ['AS-H011', 'AS-H012', 'migrat', 'axe', 'composer audit']) {
        assert.ok(pack.includes(required), `evidence pack missing gate reference: ${required}`);
    }
    assert.match(pack, /1440×900, 768×1024, 390×844, 360×800/);
});

await test('supervisor keeps the standing legal gate open with deployment disabled', () => {
    const config = JSON.parse(read('automation/config.json'));
    assert.ok(['12', '14', '15', '16'].includes(state.delivery.phase), `unexpected frontier phase ${state.delivery.phase}`);
    assert.equal(state.authorization.deployment_enabled, false);
    // Sanctioned postures: paused (config disabled, awaiting a human) or an
    // active posture (config enabled) while independent Arena-side work runs.
    if (state.lifecycle === 'paused') {
        assert.equal(config.enabled, false, 'paused supervisor must not stay enabled');
        assert.equal(state.next_action.requires_human, true, 'paused supervisor must wait on a human');
    } else {
        assert.ok(['executing', 'recovering', 'blocked'].includes(state.lifecycle), `unexpected lifecycle ${state.lifecycle}`);
        assert.equal(config.enabled, true, 'active supervisor requires enablement');
    }
    const openGates = state.human_gates.filter((gate) => gate.status === 'open').map((gate) => gate.id);
    assert.ok(openGates.includes('AS-H011'), 'AS-H011 legal-text gate must remain open as the standing pre-launch rule');
    const h012 = state.human_gates.find((gate) => gate.id === 'AS-H012');
    assert.equal(h012?.status, 'closed', 'AS-H012 must be closed once the owner runtime evidence is accepted');
});

await test('no unresolved critical or high Phase 12 blocker is recorded', () => {
    assert.deepEqual(state.blockers, []);
    const criticals = state.risks.filter((risk) => risk.severity === 'critical');
    assert.deepEqual(criticals, []);
});
