#!/usr/bin/env node
import { cpSync, existsSync, lstatSync, mkdirSync, rmSync } from 'node:fs';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const destination = process.env.RYTHME_QA_ROOT || '/tmp/rythm-qa';
const excluded = new Set(['.git', 'vendor', 'node_modules', 'public/build', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/testing', 'storage/framework/views', 'storage/logs']);

try {
    lstatSync(path.join(root, 'vendor'));
    console.error('QA PREPARE FAIL: workspace vendor entry is forbidden.');
    process.exit(2);
} catch {}

if (!destination.startsWith('/tmp/')) {
    console.error('QA PREPARE FAIL: destination must be under /tmp.');
    process.exit(2);
}
rmSync(destination, { recursive: true, force: true });
mkdirSync(destination, { recursive: true });
cpSync(root, destination, {
    recursive: true,
    filter: (source) => {
        const relative = path.relative(root, source).replaceAll('\\', '/');
        if (relative === '') return true;
        return ![...excluded].some((item) => relative === item || relative.startsWith(`${item}/`));
    }
});
for (const relative of [
    'bootstrap/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/framework/views',
    'storage/logs'
]) {
    mkdirSync(path.join(destination, relative), { recursive: true });
}
console.log(`QA COPY READY: ${destination}`);
console.log(`WORKSPACE VENDOR: ${existsSync(path.join(root, 'vendor')) ? 'FORBIDDEN' : 'ABSENT'}`);
