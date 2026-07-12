# Agent Rules

These rules apply to all future coding work in this project.

## Git Workflow

- Always check whether Git is enabled before making code changes.
- If the project is not a Git repository, initialize Git immediately.
- Keep two branches:
  - `dev` for testing, experiments, and in-progress implementation.
  - `main` for known working official code.
- Commit all feature and testing work to `dev` first.
- Move code to `main` only after it has been tested and confirmed working.
- Do not leave completed work uncommitted unless the user explicitly asks not to commit.
- Use clear commit messages that describe the actual change.
- Never overwrite or discard user changes without explicit permission.

## Documentation Rules

- Create or update documentation for every new feature added.
- Feature documentation belongs in `docs/features/`.
- Each feature document should explain:
  - Purpose of the feature.
  - Files or screens affected.
  - How to use it.
  - Any setup, database, or testing notes.
- Keep the README current when setup or usage changes.

## Progress Tracking

- Track project progress in the `progress/` folder.
- Update `progress/PROGRESS.md` whenever meaningful work is completed.
- Progress notes should include:
  - Date.
  - Summary of work completed.
  - Tests or checks performed.
  - Known blockers or next steps.

## Documentation And Clarification

- When implementation details are unclear, read the official online documentation for the relevant technology before deciding.
- Prefer official documentation over blog posts, forum answers, or copied snippets.
- For framework, library, API, payment gateway, security, or deployment questions, verify current documentation before coding.
- Record important external documentation decisions in the relevant feature document when they affect implementation.

## Code Quality

- Follow the existing PHP, MySQL, Bootstrap, and XAMPP-friendly structure.
- Keep student payment amounts controlled by backend database records, never by editable frontend fields.
- Preserve order price snapshots when handout prices change.
- Keep admin pages protected by authentication.
- Hash passwords and avoid storing sensitive secrets in committed files.
- Run syntax checks or relevant tests before committing completed work.
