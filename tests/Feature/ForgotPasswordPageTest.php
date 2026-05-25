<?php

use function Pest\Laravel\get;

it('shows marketing nav and footer on forgot password page', function () {
    $response = get('/auth/password/reset');

    $response->assertOk();
    $response->assertSee('Login');
    $response->assertSee('Historialita');
    $response->assertSee('Reset password');
});

it('shows marketing nav and footer on reset password token page', function () {
    $response = get('/auth/password/demo-token?email=test@example.com');

    $response->assertOk();
    $response->assertSee('Login');
    $response->assertSee('Historialita');
    $response->assertSeeHtml('data-auth="password-confirm-input"');
});
