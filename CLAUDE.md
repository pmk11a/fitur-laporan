Behavioral guidelines to reduce common LLM coding mistakes. Merge with project-specific instructions as needed.

Tradeoff: These guidelines bias toward caution over speed. For trivial tasks, use judgment.

1. Baca prd.md
2. Think Before Coding
Don't assume. Don't hide confusion. Surface tradeoffs.

Before implementing:

State your assumptions explicitly. If uncertain, ask.
If multiple interpretations exist, present them - don't pick silently.
If a simpler approach exists, say so. Push back when warranted.
If something is unclear, stop. Name what's confusing. Ask.
3. Simplicity First
Minimum code that solves the problem. Nothing speculative.

No features beyond what was asked.
No abstractions for single-use code.
No "flexibility" or "configurability" that wasn't requested.
No error handling for impossible scenarios.
If you write 200 lines and it could be 50, rewrite it.
Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

4. Surgical Changes
Touch only what you must. Clean up only your own mess.

When editing existing code:

Don't "improve" adjacent code, comments, or formatting.
Don't refactor things that aren't broken.
Match existing style, even if you'd do it differently.
If you notice unrelated dead code, mention it - don't delete it.
When your changes create orphans:

Remove imports/variables/functions that YOUR changes made unused.
Don't remove pre-existing dead code unless asked.
The test: Every changed line should trace directly to the user's request.

5. Goal-Driven Execution
Define success criteria. Loop until verified.

Transform tasks into verifiable goals:

"Add validation" → "Write tests for invalid inputs, then make them pass"
"Fix the bug" → "Write a test that reproduces it, then make it pass"
"Refactor X" → "Ensure tests pass before and after"
For multi-step tasks, state a brief plan:

1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.

These guidelines are working if: fewer unnecessary changes in diffs, fewer rewrites due to overcomplication, and clarifying questions come before implementation rather than after mistakes.

## 6. Memory System

Access persistent context at `.claude/memory/MEMORY.md`:

| Type | Path | When to Use |
|------|------|-------------|
| **User** | `memory/user/` | Tailor responses to developer preferences |
| **Feedback** | `memory/feedback/` | Prevent repeated mistakes |
| **Project** | `memory/project/` | Track migration progress, constraints |
| **Reference** | `memory/reference/` | External systems, documentation links |

**Read memories on:** first interaction, when user references them, or when relevant to current task.
**Update memories on:** learning user preferences, discovering patterns, completing milestones.

## 8. Before Implement: Check Memory First

**ALWAYS do this before implementing:**

```
1. Read .claude/memory/MEMORY.md
2. Check relevant memory files (sidebar, auth, etc.)
3. Verify pattern/template against memory
4. If conflict: Memory wins → tanya user
5. Then implement
```

**Common hallucination traps:**
- Sidebar hierarchy: Use **L0 level**, NOT KODEMENU prefix
- Filter -1 (Delphi): No UI filter, generate directly
- Password field: Use **UID2**, not UID

**See `.claude/memory/reference/ai-implementation-checklist.md` for full checklist.**

## 7. Migration Workflow

For Delphi → Laravel migration:
1. `/delphi-traceability-analyzer` → Scan .pas file
2. `/delphi-laravel-impl` → Generate + implement complete module

**Check `.claude/memory/feedback/migration-patterns.md` for critical gotchas before implementation.**

