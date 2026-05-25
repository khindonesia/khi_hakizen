<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\ExceptionRenderer;
use Illuminate\Http\Request;

it('renders the Laravel exception page with markdown copy support', function (): void {
    config(['app.debug' => true]);

    $request = Request::create('/debug-markdown', 'GET');

    app()->instance('request', $request);

    expect(app(ExceptionRenderer::class))->toBeInstanceOf(App\Exceptions\LaravelExceptionRenderer::class);

    $response = app(ExceptionHandler::class)->render($request, new Exception('debug test'));

    expect($response->getContent())
        ->toContain('Copy as Markdown');
});
