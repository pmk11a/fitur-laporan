---
name: ui-review
description: Parallel UI review across all user stories - discovers YAML stories and fans out bowser-qa-agents
argument-hint: [headed] [filter] [vision]
---

# UI Review Command

## Purpose

Discover user stories from YAML files, fan out parallel `bowser-qa-agent` instances to validate each story, then aggregate and report pass/fail results with screenshots.

## Variables

HEADED: $1 (default: "false" — set to "true" or "headed" for visible browser)
FILENAME_FILTER: $2 (optional filter for story files)
VISION: $3 (if contains "vision", enable vision mode)
STORIES_DIR: "ai_review/user_stories"
STORIES_GLOB: "ai_review/user_stories/*.yaml"
APP_URL: "http://localhost:3000"
SCREENSHOTS_BASE: "screenshots/bowser-qa"
AGENT_TIMEOUT: 300000

## Workflow

### Phase 1: Discover
1. Use Glob to find all files matching `STORIES_GLOB`
2. If FILENAME_FILTER provided, filter files by name
3. Read each YAML file, parse `stories` array
4. Build flat list of all stories with source tracking
5. Generate RUN_DIR with timestamp + uuid

### Phase 2: Spawn
6. Create team for parallel execution
7. Spawn `bowser-qa-agent` for each story
8. Launch all teammates in parallel

### Phase 3: Collect
9. Wait for teammate results
10. Parse reports for PASS/FAIL
11. Mark tasks as completed

### Phase 4: Cleanup & Report
12. Send shutdown requests
13. Delete team
14. Present aggregated results

## Report Format

```
# UI Review Summary

**Run:** {datetime}
**Stories:** {total} | {passed} passed | {failed} failed
**Status:** ✅ ALL PASSED | ❌ PARTIAL FAILURE

## Results

| # | Story | Source | Status | Steps |
|---|-------|--------|--------|-------|
| 1 | Login | login.yaml | ✅ PASS | 5/5 |
| 2 | Master Barang | barang.yaml | ❌ FAIL | 2/5 |

## Failures

(Only if failures exist)

### Story: {failed story}
**Source:** {filename}
**Agent Report:**
{full report}

---

## Screenshots
All screenshots: {RUN_DIR}/
```
