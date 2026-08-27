#!/usr/bin/env node
import path from 'node:path';
import process from 'node:process';
import { checkpointState, readState, validateState, writeStateAtomic } from './supervisor/state.mjs';
import { readFileSync } from 'node:fs';

const root = path.resolve(import.meta.dirname, '..');
const defaultFile = path.join(root, 'tasks', 'autonomous-supervisor-state.json');
const [command = 'status', argument = defaultFile] = process.argv.slice(2);
const file = path.resolve(argument);

try {
    if (command === 'validate') {
        const state = JSON.parse(readFileSync(file, 'utf8'));
        const result = validateState(state);
        if (!result.valid) throw new Error(result.errors.join('\n'));
        console.log(`VALID: ${file}`);
    } else if (command === 'status') {
        const state = readState(file);
        console.log(JSON.stringify({ lifecycle: state.lifecycle, phase: state.delivery.phase, chunk: state.delivery.chunk, checkpoint: state.checkpoint.id, next_action: state.next_action.id }, null, 2));
    } else if (command === 'checkpoint') {
        const state = readState(file);
        const next = checkpointState(state);
        writeStateAtomic(file, next);
        console.log(`CHECKPOINTED: ${next.checkpoint.id}`);
    } else {
        throw new Error(`Unknown command '${command}'. Use validate, status or checkpoint.`);
    }
} catch (error) {
    console.error(`SUPERVISOR STATE FAIL: ${error.message}`);
    process.exit(1);
}
