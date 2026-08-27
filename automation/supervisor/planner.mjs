const BUILD_TASKS = new Map([
    ['AS-BUILD-1-QA', { id: 'AS-BUILD-1-QA', phase: 'autonomous-supervisor-build', chunk: '1', description: 'Validate state and checkpoint engine.' }],
    ['AS-BUILD-2-AUDITOR', { id: 'AS-BUILD-2-AUDITOR', phase: 'autonomous-supervisor-build', chunk: '2', description: 'Build read-only auditor and dependency-aware planner.' }],
    ['AS-BUILD-3-RECOVERY', { id: 'AS-BUILD-3-RECOVERY', phase: 'autonomous-supervisor-build', chunk: '3', description: 'Build risk-classified retry and recovery controller.' }],
    ['AS-BUILD-4-AGENTS', { id: 'AS-BUILD-4-AGENTS', phase: 'autonomous-supervisor-build', chunk: '4', description: 'Build Agent 0 assignment, review and team-change protocol.' }],
    ['AS-BUILD-5-GATES', { id: 'AS-BUILD-5-GATES', phase: 'autonomous-supervisor-build', chunk: '5', description: 'Build evidence gate evaluator and tracker reconciliation.' }],
    ['AS-BUILD-6-RUNBOOK', { id: 'AS-BUILD-6-RUNBOOK', phase: 'autonomous-supervisor-build', chunk: '6', description: 'Build bootstrap, resume and status workflow.' }],
    ['AS-BUILD-7-SIMULATION', { id: 'AS-BUILD-7-SIMULATION', phase: 'autonomous-supervisor-build', chunk: '7', description: 'Run recovery, security and independent QA simulations.' }],
    ['AS-BUILD-8-ACTIVATION', { id: 'AS-BUILD-8-ACTIVATION', phase: 'autonomous-supervisor-build', chunk: '8', description: 'Agent 0 activation review and controlled handover.' }]
]);

export function parsePhaseStatuses(markdown) {
    const phases = [];
    for (const line of markdown.split('\n')) {
        const match = line.match(/^\|\s*(0A|0B|\d+|6A)\s*\|[^|]*\|[^|]*\|\s*(COMPLETE|IN PROGRESS|QA|PENDING|BLOCKED|INACTIVE)\s*\|/);
        if (match) phases.push({ phase: match[1], status: match[2] });
    }
    return phases;
}

export function parseExpansionNext(markdown) {
    const match = markdown.match(/^- \*\*Chunk (\d+) — NEXT:\*\*\s*(.+)$/m);
    return match ? { chunk: match[1], description: match[2].trim() } : null;
}

export function chooseNextTask({ state, audit, trackerMarkdown, expansionMarkdown }) {
    if (!audit.safe_for_planning) return { status: 'blocked', reason: 'Critical audit finding must be reconciled before planning.', findings: audit.findings.filter((item) => item.severity === 'critical') };
    if (state.lifecycle === 'building') {
        const task = BUILD_TASKS.get(state.next_action.id);
        return task ? { status: 'ready', source: 'supervisor-build-state', task } : { status: 'blocked', reason: `Unknown build action ${state.next_action.id}` };
    }

    const phases = parsePhaseStatuses(trackerMarkdown);
    const expansion = phases.find((item) => item.phase === '6A');
    if (expansion && expansion.status !== 'COMPLETE') {
        const next = parseExpansionNext(expansionMarkdown);
        return next
            ? { status: 'ready', source: 'phase-6A-contract', task: { id: `PHASE-6A-CHUNK-${next.chunk}`, phase: '6A', chunk: next.chunk, description: next.description } }
            : { status: 'blocked', reason: 'Phase 6A is open but its next chunk is not explicit.' };
    }

    const nextPhase = phases.find((item) => /^\d+$/.test(item.phase) && Number(item.phase) >= 8 && Number(item.phase) <= 17 && item.status !== 'COMPLETE');
    return nextPhase
        ? { status: nextPhase.status === 'BLOCKED' ? 'blocked' : 'ready', source: 'master-tracker', task: { id: `PHASE-${nextPhase.phase}`, phase: nextPhase.phase, chunk: 'plan', description: `Plan and execute canonical Phase ${nextPhase.phase}.` } }
        : { status: 'complete', reason: 'All authorized canonical phases through 17 are complete.' };
}
