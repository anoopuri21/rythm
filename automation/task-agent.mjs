#!/usr/bin/env node
/**
 * Retired legacy task-agent compatibility guard.
 * Current execution uses automation/supervisor.mjs and canonical governance.
 */
console.log('LEGACY TASK AGENT RETIRED: use node automation/supervisor.mjs status|resume|plan|bootstrap');
process.exit(0);
