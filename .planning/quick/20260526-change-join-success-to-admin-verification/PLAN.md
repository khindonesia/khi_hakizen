---
status: complete
description: "Change join/registration success screen to show waiting for admin verification state instead of ticket/dashboard redirect"
---

# Plan: Change Join Success to Admin Verification Pending State

## Goal
Modify the member registration success state on the join page to display a premium 'Waiting for Admin Verification' message instead of immediately showing a member pass ticket or direct dashboard access.

## Proposed Changes
- **Modify** `resources/themes/anchor/pages/join/index.blade.php`:
  - Set `verified => 0` for new member registrations to reflect pending status.
  - Disable immediate automatic login on registration.
  - Replace the ticket member pass visual in the success state with a custom styled verification status card matching KHI's color palette.
- **Modify** `tests/Feature/JoinTest.php`:
  - Update the feature tests to assert the new verification state and check that the user in the database is created with `verified = 0`.
