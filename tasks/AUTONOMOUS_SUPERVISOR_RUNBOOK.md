# Autonomous Supervisor — Bootstrap and Resume Runbook

**Status:** PRE-ACTIVATION — commands are read-only until Agent 0 completes Chunk 8

## What it can and cannot do

The Supervisor runs only during an active Arena session. It cannot keep working after Arena closes. Every transport stop resumes by inspecting committed state and actual Git state; it never assumes the previous command failed.

## Routine commands

From the repository root on Windows/Laragon or an Arena shell:

```bat
node automation/supervisor.mjs status
node automation/supervisor.mjs resume
node automation/supervisor.mjs plan
```

`status` summarizes lifecycle, branch, heads, dirty state, reconciliation and next task. `resume` is read-only and tells the agent whether to continue, inspect, reconcile or block. `plan` shows the next canonical task.

Do not use `bootstrap` as an owner command before activation. During build it deliberately exits without enabling writes:

```bat
node automation/supervisor.mjs bootstrap
```

## Resume rules

- `current`: repository matches checkpoint; continue the checkpointed action.
- `advanced`: local and remote advanced together; inspect that commit, update checkpoint, then continue.
- `inspect_interrupted_work`: preserve and inspect the dirty diff; do not reset or overwrite it.
- `reconcile_local_remote_divergence`: inspect commit graph and push outcome; do not push blindly.
- `restore_remote_visibility`: restore network/auth visibility before deciding whether a push completed.
- `blocked`: stop writes and report the exact mandatory input.

## Arena timeout

1. Start with `status` and `resume`.
2. Inspect actual output, files, Git HEAD and remote HEAD.
3. Apply the risk-classified recovery policy.
4. Checkpoint only after the outcome is verified.
5. Never rerun an import, migration, commit or push merely because Arena showed a timeout.

## Safety

Never run destructive tests against persistent MySQL/UAT. Never store secrets in checkpoint state or command output. Phase 18 and Agent 10 remain inactive until the owner explicitly authorizes deployment after Phase 17.
