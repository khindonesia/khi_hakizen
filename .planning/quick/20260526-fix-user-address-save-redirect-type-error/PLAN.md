---
status: complete
description: "Fix TypeError when saving/updating user address in Volt components due to Redirector type mismatch"
---

# Plan: Fix User Address Save Redirect Type Error

## Goal
Fix the issue where saving or updating a user address throws a `TypeError` because the Livewire `redirect()` helper returns `Livewire\Features\SupportRedirects\Redirector` while the method signature expects `?Illuminate\Http\RedirectResponse`. This exception gets caught by the catch block, displaying a "Unable to save address RajaOngkir data could not be resolved" danger notification, even though the address is saved in the database.

## Proposed Changes
- **Modify** `resources/themes/anchor/pages/user-addresses/create.blade.php`:
  - Change `public function create(): ?RedirectResponse` to `public function create(): void`.
  - Use `$this->redirect('/user-addresses');` instead of `return redirect('/user-addresses');`.
- **Modify** `resources/themes/anchor/pages/user-addresses/[address]/edit.blade.php`:
  - Change `public function update(): ?RedirectResponse` to `public function update(): void`.
  - Use `$this->redirect('/user-addresses');` instead of `return redirect('/user-addresses');`.
- **Modify** `tests/Feature/UserAddressLocationTest.php`:
  - Add `->assertRedirect('/user-addresses')` to the `create` and `update` feature tests to ensure the redirection is working correctly without any hidden TypeErrors.
