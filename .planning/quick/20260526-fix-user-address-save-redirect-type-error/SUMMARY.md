---
status: complete
date: 2026-05-26
---

# Summary: Fix User Address Save Redirect Type Error

## What Was Done
- Diagnosed the root cause of the `Unable to save address` warning. The database record was successfully saved, but returning `redirect('/user-addresses')` in Livewire 3/Volt actually returns a `Livewire\Features\SupportRedirects\Redirector` instance. This caused a PHP `TypeError` since the method signature expected `?Illuminate\Http\RedirectResponse`.
- Caught by the catch-all `\Throwable` block, this logged an error and showed the danger notification to the user, falsely claiming that "RajaOngkir data could not be resolved."
- Fixed the issue in both `create.blade.php` and `[address]/edit.blade.php` user-address Volt components by:
  - Changing the return type hints of `create()` and `update()` from `?RedirectResponse` to `void`.
  - Using `$this->redirect('/user-addresses')` instead of returning the redirector.
  - Removing `return null` from the catch blocks.
- Added `->assertRedirect('/user-addresses')` to the Pest tests in `tests/Feature/UserAddressLocationTest.php` to actively prevent regression.
- Ran Pest tests and confirmed that everything works flawlessly.
