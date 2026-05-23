---
name: dbflpass-password-field
description: DBFLPASS uses UID2 (not UID) for password storage, base64 encoded
type: reference
---

# DBFLPASS Password Field

## Critical Rule
**Always use `UID2` for password, NOT `UID`**

UID2 contains the base64-encoded plain password, while UID is typically empty.

## Example
- `UID2` = `bWFzemEx` → base64_decode → `masza1`
- `UID` = (usually empty)

## Why This Matters
Using `UID` instead of `UID2` causes login to always fail for all users.