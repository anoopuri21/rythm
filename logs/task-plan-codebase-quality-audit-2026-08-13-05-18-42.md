# Task plan — codebase-quality-audit

- **Title:** Full codebase audit & improvement — validate/verify/review EVERY file one-by-one (code quality + best practices), apply safe improvements, sanity re-review, then push
- **Picked:** 2026-08-13T05:18:42.386Z
- **Branch:** feature/dev (single-branch rule enforced)
- **PR:** #22 (no new PRs — auto-updates)
- **Status:** planning → implement → test → commit → push

## Checklist
- [ ] Implement per AGENT_RULES_STRICT (design system, strict types, attributes, security mandate)
- [ ] `npm run build` green
- [ ] `php artisan test` green
- [ ] Commit on feature/dev, push, update tasks.json status
