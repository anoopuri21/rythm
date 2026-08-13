#!/usr/bin/env node
/**
 * task-agent.mjs — automation helper for the Rythme single-branch workflow.
 *
 * RULES (hard-coded, non-negotiable):
 *   1. Branch MUST be `feature/dev` — never create/switch to another branch.
 *   2. NEVER open a PR — the single PR #22 (feature/dev -> main) already
 *      exists and auto-updates on every push.
 *   3. tasks.json is the source of truth for what to do next.
 *
 * The actual implementation (design/code/test) is executed by the AI agent;
 * this script is the discipline layer: it validates branch, picks the next
 * task, runs health + gates, writes plan/report logs, and commits on
 * feature/dev only.
 */
import { execSync } from 'node:child_process';
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import path from 'node:path';

const ROOT = path.resolve(import.meta.dirname, '..');
const TASKS = path.join(ROOT, 'tasks', 'tasks.json');
const LOGS = path.join(ROOT, 'logs');
const BRANCH = 'feature/dev';

const run = (cmd) => execSync(cmd, { cwd: ROOT, encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'] }).trim();
const log = (msg) => {
    const line = `[${new Date().toISOString()}] ${msg}\n`;
    console.log(line.trim());
    if (!existsSync(LOGS)) mkdirSync(LOGS, { recursive: true });
    writeFileSync(path.join(LOGS, 'task-agent.log'), line, { flag: 'a' });
};

// ── 1. Branch guard ───────────────────────────────────────────────────────
let branch;
try { branch = run('git rev-parse --abbrev-ref HEAD'); } catch { log('FAIL: not a git repo'); process.exit(1); }

if (branch !== BRANCH) {
    log(`FAIL: on branch '${branch}' — expected '${BRANCH}'. Aborting (single-branch rule).`);
    process.exit(1);
}
log(`branch OK: ${branch}`);

// ── 2. Read tasks.json ────────────────────────────────────────────────────
let state;
try { state = JSON.parse(readFileSync(TASKS, 'utf-8')); } catch (e) { log(`FAIL: cannot read tasks.json: ${e.message}`); process.exit(1); }

const actionable = state.tasks.filter((t) => t.status === 'pending' && !t.on_hold);
const next = actionable.find((t) => t.next) ?? actionable[0];
if (!next) {
    log('no pending tasks — all done. waiting for new directives.');
    process.exit(0);
}
log(`next task: ${next.id} — ${next.title}`);

// ── 3. Health + gates ─────────────────────────────────────────────────────
const cfg = JSON.parse(readFileSync(path.join(ROOT, 'automation', 'config.json'), 'utf-8'));
for (const cmd of cfg.health) {
    try { run(cmd); log(`health OK: ${cmd}`); }
    catch (e) { log(`HEALTH FAIL: ${cmd} — ${e.message.split('\n')[0]}`); process.exit(1); }
}
for (const cmd of cfg.gates) {
    try { const out = run(cmd); log(`gate OK: ${cmd}`); log(out.split('\n').filter(l => l.includes('Tests:')).join(' ') || 'no summary'); }
    catch (e) { log(`GATE FAIL: ${cmd}`); process.exit(1); }
}

// ── 4. Plan/report log for the agent ──────────────────────────────────────
const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
const plan = path.join(LOGS, `task-plan-${next.id}-${stamp}.md`);
writeFileSync(plan, [
    `# Task plan — ${next.id}`,
    '',
    `- **Title:** ${next.title}`,
    `- **Picked:** ${new Date().toISOString()}`,
    `- **Branch:** ${branch} (single-branch rule enforced)`,
    `- **PR:** #22 (no new PRs — auto-updates)`,
    `- **Status:** planning → implement → test → commit → push`,
    '',
    '## Checklist',
    '- [ ] Implement per AGENT_RULES_STRICT (design system, strict types, attributes, security mandate)',
    '- [ ] `npm run build` green',
    '- [ ] `php artisan test` green',
    '- [ ] Commit on feature/dev, push, update tasks.json status',
    '',
].join('\n'));
log(`plan written: ${plan}`);

log('task-agent cycle complete — agent implements next (above).');
