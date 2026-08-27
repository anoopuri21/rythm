const BASE_GATES = ['diff_check', 'independent_review', 'tracker_update'];
const PROFILE_GATES = Object.freeze({
    php: ['php_syntax', 'style', 'focused_tests'],
    ui: ['production_build', 'responsive_review', 'accessibility_review'],
    migration: ['database_review', 'isolated_migration_cycle'],
    dependency: ['dependency_audit'],
    security: ['security_review'],
    automation: ['automation_tests', 'safety_review'],
    documentation: []
});

export function requiredGates({ profiles = [], phaseGate = false }) {
    const required = new Set(BASE_GATES);
    for (const profile of profiles) {
        const gates = PROFILE_GATES[profile];
        if (!gates) throw new Error(`Unknown gate profile '${profile}'`);
        gates.forEach((gate) => required.add(gate));
    }
    if (phaseGate) required.add('full_regression');
    return [...required].sort();
}

const validTime = (value) => typeof value === 'string' && !Number.isNaN(Date.parse(value));

export function evaluateGateSet({ required, gates, currentCommit, workingTreeDigest, taskStartedAt, evidenceExists = () => true }) {
    const results = [];
    const byName = new Map((gates ?? []).map((gate) => [gate.name, gate]));
    for (const name of required) {
        const gate = byName.get(name);
        const errors = [];
        if (!gate) errors.push('missing');
        else {
            if (!['passed', 'failed', 'blocked', 'not_applicable'].includes(gate.outcome)) errors.push('invalid outcome');
            if (gate.outcome !== 'passed') errors.push(`outcome ${gate.outcome}`);
            if (typeof gate.evidence !== 'string' || gate.evidence.trim() === '') errors.push('evidence missing');
            else if (!evidenceExists(gate.evidence)) errors.push('evidence not found');
            if (!validTime(gate.verified_at)) errors.push('verification timestamp invalid');
            else if (validTime(taskStartedAt) && Date.parse(gate.verified_at) < Date.parse(taskStartedAt)) errors.push('evidence predates task');
            const commitBound = gate.commit === currentCommit;
            const treeBound = workingTreeDigest && gate.working_tree_digest === workingTreeDigest;
            if (!commitBound && !treeBound) errors.push('evidence is not bound to current commit or working tree');
        }
        results.push({ name, passed: errors.length === 0, errors });
    }
    const extras = [...byName.keys()].filter((name) => !required.includes(name));
    return { passed: results.every((result) => result.passed), results, extras };
}

export function reconcileTrackerStatus({ requestedStatus, gateEvaluation, agent0Decision, hasCriticalBlocker = false }) {
    if (hasCriticalBlocker) return { status: 'BLOCKED', reason: 'A critical blocker remains open.' };
    if (requestedStatus !== 'COMPLETE') return { status: requestedStatus, reason: 'Completion was not requested.' };
    if (!gateEvaluation?.passed) return { status: 'QA', reason: 'Mandatory gates are incomplete or failed.' };
    if (agent0Decision?.actor !== 'Agent 0' || agent0Decision?.status !== 'accepted' || !agent0Decision?.reason) {
        return { status: 'QA', reason: 'Agent 0 has not explicitly accepted completion.' };
    }
    return { status: 'COMPLETE', reason: agent0Decision.reason };
}

export function aggregatePhaseGates(chunks) {
    if (!Array.isArray(chunks) || chunks.length === 0) return { status: 'QA', complete: false, reason: 'No chunk evidence supplied.' };
    const blocked = chunks.find((chunk) => chunk.status === 'BLOCKED');
    if (blocked) return { status: 'BLOCKED', complete: false, reason: `Chunk ${blocked.id} is blocked.` };
    const incomplete = chunks.filter((chunk) => chunk.status !== 'COMPLETE' || chunk.gates_passed !== true);
    if (incomplete.length) return { status: 'QA', complete: false, reason: `Incomplete chunks: ${incomplete.map((chunk) => chunk.id).join(', ')}` };
    return { status: 'QA', complete: false, reason: 'All chunks passed; full phase regression and Agent 0 acceptance are still required.' };
}
