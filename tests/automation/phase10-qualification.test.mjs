import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

const master = () => read('tasks/MASTER_PROJECT_TRACKER.md');
const sequence = () => read('tasks/CANONICAL_PHASE_SEQUENCE.md');
const state = () => JSON.parse(read('tasks/autonomous-supervisor-state.json'));
const config = () => JSON.parse(read('automation/config.json'));

// Lifecycle and enablement move together as the owner activates/pauses Auto Mode.
// The safety invariants below must hold in every sanctioned posture.
const SANCTIONED_LIFECYCLES = ['executing', 'recovering', 'blocked', 'paused'];

const assertPostureConsistency = () => {
    const current = state();
    const cfg = config();
    assert.ok(SANCTIONED_LIFECYCLES.includes(current.lifecycle), `lifecycle ${current.lifecycle} is not sanctioned`);
    if (current.lifecycle === 'paused') {
        assert.equal(cfg.enabled, false, 'a paused supervisor must not stay enabled');
        assert.equal(current.next_action.requires_human, true, 'a paused supervisor must be waiting on a human');
    } else {
        assert.equal(cfg.enabled, true, 'an executing/recovering/blocked supervisor requires enablement');
    }
    assert.equal(current.authorization.deployment_enabled, false);
    assert.equal(current.git.branch, 'rhythm-uat');
};

test('canonical planning files record accepted Phase 11 and active Phase 12', () => {
  assert.match(master(), /PHASES 0–11 AND 6A COMPLETE \/ PHASE 12 IN PROGRESS/);
  assert.match(sequence(), /\| 7 \|[^\n]+\| COMPLETE \|/);
  assert.match(sequence(), /\| 8 \|[^\n]+\| COMPLETE \|/);
  assert.match(sequence(), /\| 9 \|[^\n]+\| COMPLETE \|/);
  assert.match(sequence(), /\| 10 \|[^\n]+\| COMPLETE \|/);
  assert.match(sequence(), /\| 11 \|[^\n]+\| COMPLETE \|/);
  const current = state();
  assert.equal(current.delivery.phase, '12');
  assert.equal(current.delivery.status, 'in_progress');
  assertPostureConsistency();
});

test('Phase 10 qualification evidence stays non-destructive and owner-reported', () => {
  const checklist = read('docs/phase10-qualification.md');
  const gate = read('tasks/PHASE_10_CHUNK_5_QUALIFICATION_GATE.md');

  for (const required of [
    'isolated MySQL 8',
    'FulfillmentDomainTest.php',
    'ReturnRequestDomainTest.php',
    'CheckoutTest.php',
    'full PHP suite',
    'Customer parcel rendering',
    'Return/RMA disabled-default gate',
    'Tax disabled-default and snapshot matrix',
    'Professional approval record',
    'Agent 0 accepts',
  ]) assert.match(checklist, new RegExp(required.replace('/', '\\/'), 'i'));

  assert.match(checklist, /Do \*\*not\*\* use `migrate:fresh`, `db:wipe`, rollback, refresh, or seeders against persistent UAT/);
  assert.match(checklist, /Enablement authorized: `NO`/);
  assert.match(checklist, /cannot authorize Phase 18 or activate Agent 10/);
  assert.match(gate, /owner-reported qualification accepted/i);
  assert.match(gate, /not a local Arena execution/i);
  assert.match(gate, /Phase 10 as \*\*COMPLETE\*\*/i);
});
