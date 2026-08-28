import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { existsSync, lstatSync, readFileSync } from 'node:fs';
import path from 'node:path';
import { readState } from './state.mjs';

const runGit = (root, args) => execFileSync('git', args, { cwd: root, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }).trim();
const sha256 = (file) => createHash('sha256').update(readFileSync(file)).digest('hex');

export function auditProject(root) {
    const configFile = path.join(root, 'automation', 'config.json');
    const config = JSON.parse(readFileSync(configFile, 'utf8'));
    const stateFile = path.join(root, 'tasks', 'autonomous-supervisor-state.json');
    const state = readState(stateFile);
    const branch = runGit(root, ['branch', '--show-current']);
    const localHead = runGit(root, ['rev-parse', 'HEAD']);
    let remoteHead = null;
    let remoteAvailable = true;
    try { remoteHead = runGit(root, ['rev-parse', `origin/${branch}`]); } catch { remoteAvailable = false; }
    const changes = runGit(root, ['status', '--porcelain=v1']);
    const findings = [];

    if (branch !== config.branch) findings.push({ severity: 'critical', code: 'WRONG_BRANCH', detail: `Expected ${config.branch}; found ${branch}` });
    if (config.enabled === true && state.lifecycle !== 'executing' && state.lifecycle !== 'recovering' && state.lifecycle !== 'blocked' && state.lifecycle !== 'complete') findings.push({ severity: 'critical', code: 'PREMATURE_AUTOMATION_ENABLE', detail: 'Supervisor cannot be enabled during inactive/planning/building lifecycle.' });
    if (state.authorization.deployment_enabled) findings.push({ severity: 'critical', code: 'DEPLOYMENT_ENABLED', detail: 'Phase 18 is not authorized.' });
    if (remoteAvailable && localHead !== remoteHead) findings.push({ severity: 'warning', code: 'HEAD_DIVERGENCE', detail: 'Local and remote branch heads differ; reconcile before writes.' });

    const sources = config.authoritative_sources.map((relative) => {
        const file = path.join(root, relative);
        if (!existsSync(file)) {
            findings.push({ severity: 'critical', code: 'MISSING_AUTHORITY', detail: relative });
            return { path: relative, exists: false, sha256: null };
        }
        return { path: relative, exists: true, sha256: sha256(file) };
    });

    const vendor = path.join(root, 'vendor');
    let vendorEntry = null;
    try { vendorEntry = lstatSync(vendor); } catch {}
    const vendorState = vendorEntry === null
        ? { status: 'absent', type: null }
        : { status: 'forbidden-workspace-entry', type: vendorEntry.isSymbolicLink() ? 'symlink' : (vendorEntry.isDirectory() ? 'directory' : 'file') };
    if (vendorEntry !== null) findings.push({ severity: 'critical', code: 'WORKSPACE_VENDOR_ENTRY', detail: `Workspace vendor ${vendorState.type} is forbidden; use an external QA copy.` });

    return {
        audited_at: new Date().toISOString(),
        mode: 'read-only',
        git: { branch, local_head: localHead, remote_head: remoteHead, remote_available: remoteAvailable, working_tree_clean: changes === '', changes: changes ? changes.split('\n') : [] },
        state: { lifecycle: state.lifecycle, checkpoint: state.checkpoint.id, next_action: state.next_action.id },
        authority_sources: sources,
        vendor: vendorState,
        findings,
        safe_for_planning: !findings.some((finding) => finding.severity === 'critical')
    };
}
