<?php

use function Pest\Laravel\get;

it('renders the login fields directly on the login page', function () {
    $response = get('/auth/login');

    $response->assertOk();
    $response->assertSeeHtml('data-auth="email-input"');
    $response->assertSeeHtml('data-auth="password-input"');
    $response->assertSeeHtml('data-auth="forgot-password-link"');
    $response->assertDontSeeHtml('data-auth="edit-email-button"');
    $response->assertDontSee('Akses cepat');
    $response->assertDontSee('Terhubung penuh');
});
