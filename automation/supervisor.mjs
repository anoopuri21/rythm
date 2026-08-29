#!/usr/bin/env node
import { readFileSync } from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { auditProject } from './supervisor/auditor.mjs';
import { bootstrapDecision, reconcileCheckpoint, supervisorStatus } from './supervisor/lifecycle.mjs';
import { chooseNextTask } from './supervisor/planner.mjs';
import { readState } from './supervisor/state.mjs';

const root = path.resolve(import.meta.dirname, '..');
const command = process.argv[2] ?? 'status';
const readContext = () => {
    const audit = auditProject(root);
    const state = readState(path.join(root, 'tasks', 'autonomous-supervisor-state.json'));
    const config = JSON.parse(readFileSync(path.join(root, 'automation', 'config.json'), 'utf8'));
    const plan = chooseNextTask({ state, audit, trackerMarkdown: readFileSync(path.join(root, 'tasks', 'MASTER_PROJECT_TRACKER.md'), 'utf8'), expansionMarkdown: readFileSync(path.join(root, 'tasks', 'PHASE_6A_CATALOGUE_EXPANSION_PLAN.md'), 'utf8') });
    return { audit, state, config, plan };
};

try {
    if (command === 'audit') console.log(JSON.stringify(auditProject(root), null, 2));
    else if (command === 'plan') console.log(JSON.stringify(readContext().plan, null, 2));
    else if (command === 'resume') {
        const { state, audit } = readContext();
        const result = reconcileCheckpoint(state, audit);
        console.log(JSON.stringify(result, null, 2));
        if (result.status === 'blocked') process.exitCode = 2;
    } else if (command === 'status') console.log(JSON.stringify(supervisorStatus(readContext()), null, 2));
    else if (command === 'bootstrap') {
        const result = bootstrapDecision(readContext());
        console.log(JSON.stringify(result, null, 2));
        if (!result.allowed) process.exitCode = result.status === 'building' ? 3 : 2;
    } else throw new Error(`Unknown command '${command}'. Use audit, plan, status, resume or bootstrap.`);
} catch (error) {
    console.error(`SUPERVISOR FAIL: ${error.message}`);
    process.exit(1);
}
