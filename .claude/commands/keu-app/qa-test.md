---
name: qa-test
description: Run QA tests for Keu-app using bowser-qa-agent with user stories
argument-hint: [story-name] [headed] [vision]
---

# QA Test Command

## Purpose

Execute QA tests for Keu-app using the bowser-qa-agent. Run user stories, capture screenshots, and report pass/fail results.

## Variables

HEADED: $2 (default: "false" — set to "true" or "headed" for visible browser windows)
STORY: $1 (specific story name to run, or "all" for all stories)
VISION: $3 (if "vision", enable vision mode)
STORIES_DIR: "ai_review/user_stories"
APP_URL: "http://localhost:3000"
SCREENSHOTS_DIR: "./screenshots/bowser-qa"

## Workflow

1. If STORY is "all" or empty:
   - Discover all YAML files in `STORIES_DIR`
   - Execute each story sequentially
2. If STORY is specified:
   - Find matching YAML file containing the story
   - Execute only that story
3. For each story:
   - Open browser with session name derived from story
   - Execute workflow steps
   - Take screenshot at each step
   - Evaluate PASS/FAIL
   - Close session
4. Report aggregated results

## Usage

```bash
# Run all stories
/qa-test all

# Run specific story
/qa-test login

# Run with visible browser
/qa-test login true

# Run with vision mode
/qa-test login false vision
```

## Report Format

```
# QA Test Results

**Run:** {datetime}
**Stories:** {total} | {passed} passed | {failed} failed
**Status:** ✅ ALL PASSED | ❌ PARTIAL FAILURE

## Results

| # | Story | Status | Steps |
|---|-------|--------|-------|
| 1 | Login | ✅ PASS | 5/5 |
| 2 | Master Barang | ❌ FAIL | 2/5 |

## Screenshots
{screenshots_dir}/{run-id}/
```
