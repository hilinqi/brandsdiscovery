# Pi Agent Execution Instruction

## Mandatory Workflow
1. Read the global rules and relevant module documents.
2. Identify every affected theme, plugin, API and database interface.
3. Produce an impact analysis.
4. Implement all affected changes consistently.
5. Update versions and changelogs.
6. Run automated and integration tests.
7. Repeat fixes until acceptance criteria pass.
8. Package and report.

## Never
- Ask the user to repeatedly upload routine defective builds for basic testing.
- Put business logic in the theme.
- Modify only one component when shared interfaces change.
- Hardcode production URLs, R2 URLs, secrets or API keys.
- add non-MVP features.
- release inconsistent versions.

## Required Delivery
- Theme ZIP
- Three plugin ZIPs
- Compatibility matrix
- Migration report
- Test report
- Changed-file list
- Version report
- Rollback steps
