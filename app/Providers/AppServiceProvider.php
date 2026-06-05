<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend(\Illuminate\Contracts\Foundation\ExceptionRenderer::class, function (\Illuminate\Contracts\Foundation\ExceptionRenderer $renderer) {
            return new \App\Exceptions\LaravelExceptionRenderer($renderer);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment() == 'production') {
            $this->app['request']->server->set('HTTPS', true);
        }

        Gate::define('panel.access', function ($user) {
            return $user->hasAnyPermission([
                'panel.access',
            ]);
        });

        $this->setSchemaDefaultLength();

        Validator::extend('base64image', function ($attribute, $value, $parameters, $validator) {
            $explode = explode(',', $value);
            $allow = ['png', 'jpg', 'svg', 'jpeg'];
            $format = str_replace(
                [
                    'data:image/',
                    ';',
                    'base64',
                ],
                [
                    '',
                    '',
                    '',
                ],
                $explode[0]
            );

            // check file format
            if (!in_array($format, $allow)) {
                return false;
            }

            // check base64 format
            if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[1])) {
                return false;
            }

            return true;
        });
    }

    private function setSchemaDefaultLength(): void
    {
        try {
            Schema::defaultStringLength(191);
        } catch (\Exception $exception) {
        }
    }
}
