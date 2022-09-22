<?php

namespace Vellichore\Testing;

use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse as BaseTestResponse;

class LaravelAssertionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (!BaseTestResponse::hasMacro('assertRedirection')) {
            BaseTestResponse::macro('assertRedirection', function ($value) {
                $response = new TestResponse($this);

                return $response->assertRedirect($value);
            });
        }
    }
}
