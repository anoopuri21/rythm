import { createHash } from 'node:crypto';

export const AGENTS = new Map([
    ['Agent 0', 'Project Lead'], ['Agent 1', 'UI/UX'], ['Agent 2', 'Frontend'],
    ['Agent 3', 'Laravel Backend'], ['Agent 4', 'Database'], ['Agent 5', 'Product Data'],
    ['Agent 6', 'Filament Admin'], ['Agent 7', 'Feature Completeness'],
    ['Agent 8', 'Security and Performance'], ['Agent 9', 'QA'], ['Agent 10', 'Deployment'],
    ['Agent 11', 'Solution Architecture'], ['Agent 12', 'Financial Integrity'],
    ['Agent 13', 'Accessibility and SEO'], ['Agent 14', 'Notifications'],
    ['Agent 15', 'India Commerce Operations']
]);

const nonEmpty = (value) => typeof value === 'string' && value.trim() !== '';
const unique = (values) => new Set(values).size === values.length;

export function validateAssignment(assignment, { deploymentEnabled = false } = {}) {
    const errors = [];
    if (!nonEmpty(assignment?.task_id)) errors.push('task_id is required');
    if (assignment?.accountable !== 'Agent 0') errors.push('Agent 0 must remain accountable');
    for (const key of ['primary', 'reviewers']) {
        if (!Array.isArray(assignment?.[key]) || assignment[key].length === 0) errors.push(`${key} must contain at least one agent`);
        else {
            if (!unique(assignment[key])) errors.push(`${key} must not contain duplicates`);
            for (const agent of assignment[key]) if (!AGENTS.has(agent)) errors.push(`${key} contains unknown ${agent}`);
        }
    }
    const overlap = (assignment?.primary ?? []).filter((agent) => (assignment?.reviewers ?? []).includes(agent));
    if (overlap.length) errors.push(`reviewers must be independent of primary agents: ${overlap.join(', ')}`);
    if ((assignment?.primary ?? []).includes('Agent 0')) errors.push('Agent 0 accepts and integrates; a specialist must own primary delivery');
    if (!deploymentEnabled && [...(assignment?.primary ?? []), ...(assignment?.reviewers ?? [])].includes('Agent 10')) errors.push('Agent 10 is inactive until explicit Phase 18 deployment activation');
    return { valid: errors.length === 0, errors };
}

export function evaluateCompletion({ assignment, specialist_claims: claims, gates, decision }, options = {}) {
    const assignmentResult = validateAssignment(assignment, options);
    const errors = [...assignmentResult.errors];
    if (!Array.isArray(claims) || claims.length === 0) errors.push('specialist_claims are required');
    if (!Array.isArray(gates) || gates.length === 0) errors.push('independent gates are required');
    else if (gates.some((gate) => gate.outcome !== 'passed' || !nonEmpty(gate.evidence))) errors.push('every gate must pass with evidence');
    if (decision?.actor !== 'Agent 0') errors.push('only Agent 0 may decide completion');
    if (decision?.status !== 'accepted') errors.push('Agent 0 must explicitly accept completion');
    if (!nonEmpty(decision?.reason)) errors.push('completion decision reason is required');
    return { complete: errors.length === 0, errors };
}

function proposalDigest(proposal) {
    return createHash('sha256').update(JSON.stringify(proposal)).digest('hex').slice(0, 16);
}

export function createTeamChangeProposal({ change_type: type, target_agent: target, reason, capability, overlap, rollback }) {
    const allowed = new Set(['add', 'remove', 'reassign', 'capability_change']);
    if (!allowed.has(type)) throw new Error('Unsupported team change type');
    for (const [key, value] of Object.entries({ target_agent: target, reason, capability, overlap, rollback })) {
        if (!nonEmpty(value)) throw new Error(`${key} is required`);
    }
    if (target === 'Agent 0' && type === 'remove') throw new Error('Agent 0 cannot be removed');
    const body = { change_type: type, target_agent: target, reason, capability, overlap, rollback, proposed_by: 'Autonomous Supervisor', status: 'proposed' };
    return { id: `TEAM-${proposalDigest(body)}`, ...body, decision: null };
}

export function decideTeamChange(proposal, { actor, status, reason }, { deploymentEnabled = false } = {}) {
    if (proposal?.status !== 'proposed') throw new Error('Only a proposed change may be decided');
    if (actor !== 'Agent 0') throw new Error('Only Agent 0 may approve or reject team changes');
    if (!['approved', 'rejected'].includes(status)) throw new Error('Decision must be approved or rejected');
    if (!nonEmpty(reason)) throw new Error('Decision reason is required');
    if (status === 'approved' && proposal.target_agent === 'Agent 10' && !deploymentEnabled) throw new Error('Agent 10 cannot be activated before explicit deployment authorization');
    return { ...proposal, status, decision: { actor, reason } };
}
