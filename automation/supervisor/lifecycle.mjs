export function reconcileCheckpoint(state, audit) {
    const actual = audit.git;
    const recorded = state.git;
    if (!actual.remote_available) return { status: 'blocked', action: 'restore_remote_visibility', reason: 'Remote HEAD is unavailable; push outcome cannot be reconciled.' };
    if (actual.branch !== recorded.branch) return { status: 'blocked', action: 'return_to_authorized_branch', reason: `Checkpoint expects ${recorded.branch}; repository is on ${actual.branch}.` };
    if (!actual.working_tree_clean) return { status: 'inspect', action: 'inspect_interrupted_work', reason: 'Working tree has changes; determine whether they are expected before any write.' };
    if (actual.local_head !== actual.remote_head) return { status: 'inspect', action: 'reconcile_local_remote_divergence', reason: 'Local and remote heads differ.' };
    if (actual.local_head === recorded.local_head && actual.remote_head === recorded.remote_head) return { status: 'current', action: 'continue_checkpointed_action', reason: 'Repository matches the recorded checkpoint baseline.' };
    return { status: 'advanced', action: 'accept_verified_advance', reason: 'Local and remote advanced together after the checkpoint; inspect the commit, update state, then continue.' };
}

export function supervisorStatus({ state, audit, plan, config }) {
    const reconciliation = reconcileCheckpoint(state, audit);
    const activation = config.enabled === true && state.lifecycle !== 'building' && state.lifecycle !== 'inactive' ? 'active' : 'not_active';
    return {
        activation,
        lifecycle: state.lifecycle,
        branch: audit.git.branch,
        local_head: audit.git.local_head,
        remote_head: audit.git.remote_head,
        working_tree_clean: audit.git.working_tree_clean,
        audit_safe: audit.safe_for_planning,
        reconciliation,
        next: plan
    };
}

export function bootstrapDecision({ state, audit, plan, config }) {
    if (!audit.safe_for_planning) return { allowed: false, status: 'blocked', reason: 'Critical audit findings must be resolved.' };
    const reconciliation = reconcileCheckpoint(state, audit);
    if (['blocked', 'inspect'].includes(reconciliation.status)) return { allowed: false, status: reconciliation.status, reason: reconciliation.reason, action: reconciliation.action };
    if (state.lifecycle === 'paused') return { allowed: false, status: 'paused', reason: 'Owner paused Auto Mode; manual phase-by-phase execution is required.', next: plan };
    if (config.enabled !== true) return { allowed: false, status: 'building', reason: 'Supervisor activation QA has not passed; write execution remains disabled.', next: plan };
    if (state.lifecycle === 'inactive' || state.lifecycle === 'building') return { allowed: false, status: state.lifecycle, reason: 'Supervisor lifecycle is not executable.', next: plan };
    if (plan.status !== 'ready') return { allowed: false, status: plan.status, reason: plan.reason ?? 'No ready task.' };
    return { allowed: true, status: 'ready', reason: 'Audit, checkpoint and planner are consistent.', next: plan.task };
}
