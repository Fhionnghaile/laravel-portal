<?php

namespace App\Providers;

use App\Domain\FakeVeritasClient;
use App\Domain\VeritasClient;
use App\Models\CredentialRequest;
use App\Policies\CredentialRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Production binds an HTTP-backed client via config; the demo uses the fake.
        $this->app->bind(VeritasClient::class, function () {
            $driver = config('veritas.driver', 'fake');
            if ($driver === 'http' && class_exists(\App\Services\HttpVeritasClient::class)) {
                return new \App\Services\HttpVeritasClient(config('veritas.base_url'), config('veritas.token'));
            }
            return new FakeVeritasClient(config('veritas.revoked', []));
        });
    }

    public function boot(): void
    {
        Gate::policy(CredentialRequest::class, CredentialRequestPolicy::class);
        Gate::define('viewAny', fn ($user) => $user->isEmployer() || $user->isAdmin());
    }
}
