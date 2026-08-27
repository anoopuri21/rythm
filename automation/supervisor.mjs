#!/usr/bin/env node
import { readFileSync } from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { auditProject } from './supervisor/auditor.mjs';
import { chooseNextTask } from './supervisor/planner.mjs';
import { readState } from './supervisor/state.mjs';

const root = path.resolve(import.meta.dirname, '..');
const command = process.argv[2] ?? 'status';
try {
    const audit = auditProject(root);
    if (command === 'audit') console.log(JSON.stringify(audit, null, 2));
    else if (command === 'plan') {
        const state = readState(path.join(root, 'tasks', 'autonomous-supervisor-state.json'));
        const result = chooseNextTask({ state, audit, trackerMarkdown: readFileSync(path.join(root, 'tasks', 'MASTER_PROJECT_TRACKER.md'), 'utf8'), expansionMarkdown: readFileSync(path.join(root, 'tasks', 'PHASE_6A_CATALOGUE_EXPANSION_PLAN.md'), 'utf8') });
        console.log(JSON.stringify(result, null, 2));
        if (result.status === 'blocked') process.exitCode = 2;
    } else throw new Error(`Unknown command '${command}'. Use audit or plan.`);
} catch (error) {
    console.error(`SUPERVISOR FAIL: ${error.message}`);
    process.exit(1);
}
