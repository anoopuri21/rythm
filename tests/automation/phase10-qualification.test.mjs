import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');

test('canonical planning files consistently identify Phase 10 qualification as current', () => {
  const master = read('tasks/MASTER_PROJECT_TRACKER.md');
  const sequence = read('tasks/CANONICAL_PHASE_SEQUENCE.md');
  const state = JSON.parse(read('tasks/autonomous-supervisor-state.json'));

  assert.match(master, /PHASES 0–9 AND 6A COMPLETE \/ PHASE 10 IN PROGRESS/);
  assert.match(sequence, /\| 7 \|[^\n]+\| COMPLETE \|/);
  assert.match(sequence, /\| 8 \|[^\n]+\| COMPLETE \|/);
  assert.match(sequence, /\| 9 \|[^\n]+\| COMPLETE \|/);
  assert.match(sequence, /\| 10 \|[^\n]+\| IN PROGRESS \|/);
  assert.equal(state.delivery.phase, '10');
  assert.equal(state.delivery.status, 'qa');
  assert.equal(state.next_action.requires_human, true);
  assert.equal(state.authorization.deployment_enabled, false);
});

test('Phase 10 qualification checklist is non-destructive and evidence complete', () => {
  const checklist = read('docs/phase10-qualification.md');

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
});
