<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;

class TestingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!$this->app->runningUnitTests()) return;

        AssertableInertia::macro('hasResource', function (string $key, JsonResource $resource) {
            //check if property exists
            $this->has($key);
            //check prop by key and the actual data
            expect($this->prop($key))->toEqual($resource->response()->getData(true));


            return $this;
        });

        AssertableInertia::macro('hasPaginatedResource', function (string $key, ResourceCollection $resource) {
            //reuse the hasResource function to check all the data
            $this->hasResource("{$key}.data", $resource);
            //check of the keys exists to know if it is a paginated collection
            expect($this->prop($key))->toHaveKeys(['data', 'links', 'meta']);

            return $this;
        });

        TestResponse::macro('assertHasResource', function (string $key, JsonResource $resource) {
            return $this->assertInertia(fn (AssertableInertia $inertia) => $inertia->hasResource($key, $resource));
        });

        TestResponse::macro('assertHasPaginatedResource', function (string $key, ResourceCollection $resource) {
            return $this->assertInertia(fn (AssertableInertia $inertia) => $inertia->hasPaginatedResource($key, $resource));
        });

        TestResponse::macro('assertComponent', function (string $component) {
            return $this->assertInertia(fn (AssertableInertia $inertia) => $inertia->component($component, true));
        });
    }
}
