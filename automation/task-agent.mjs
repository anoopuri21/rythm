#!/usr/bin/env node
/**
 * Legacy task-agent compatibility guard.
 *
 * This script previously executed against stale `feature/dev` and
 * `tasks/tasks.json` assumptions. It is intentionally write-disabled until the
 * versioned Autonomous Supervisor is implemented, tested and activated by
 * Agent 0. It must not pick tasks, run gates, write logs, commit or push.
 */
import { readFileSync } from 'node:fs';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const configPath = path.join(root, 'automation', 'config.json');

let config;

try {
    config = JSON.parse(readFileSync(configPath, 'utf8'));
} catch (error) {
    console.error(`SUPERVISOR GUARD FAIL: cannot read ${configPath}: ${error.message}`);
    process.exit(1);
}

if (config.enabled !== true) {
    console.log(`SUPERVISOR INACTIVE: ${config.status ?? 'DISABLED'}`);
    console.log(config.reason ?? 'Activation QA has not passed.');
    process.exit(0);
}

console.error('SUPERVISOR GUARD FAIL: legacy task-agent cannot be enabled. Use the tested Supervisor entry point after Agent 0 activation.');
process.exit(1);
