# Employer Portal (Laravel)

[![CI](https://github.com/Fhionnghaile/laravel-portal/actions/workflows/ci.yml/badge.svg)](https://github.com/Fhionnghaile/laravel-portal/actions/workflows/ci.yml) [![OpenSSF Scorecard](https://api.scorecard.dev/projects/github.com/Fhionnghaile/laravel-portal/badge)](https://scorecard.dev/viewer/?uri=github.com/Fhionnghaile/laravel-portal)

A PHP/Laravel portal where employers request candidate credentials, candidates grant a
**scoped, time-limited** share, and the portal verifies the credential via **Veritas**.
Complements the Veritas verifiable-credentials service.

> **What it proves:** Laravel (models, migrations, policies, queued jobs, Blade),
> PHP domain design, and a clean mockable integration boundary.

## Flow

1. An **employer** creates a `CredentialRequest` for a candidate (email + requested
   scopes + TTL). A share token is generated.
2. The **candidate** approves — a scoped, time-limited grant (`ShareGrant`).
3. The portal dispatches `VerifyCredentialJob`, which enforces the grant, calls the
   `VeritasClient`, and records a `VerificationResult` (`valid` / `invalid` / `revoked`
   / `error`).

## Design

- **Framework-independent domain** (`app/Domain/`): `ShareGrant` (scope + expiry logic),
  `VerificationOutcome`, and the `VeritasClient` port with a deterministic
  `FakeVeritasClient`. This is the bug-prone logic, so it is isolated and unit-tested
  directly.
- **Authorization** via a real policy (`CredentialRequestPolicy`): only employers/admins
  create requests; an employer sees only their own.
- **Queued verification** (`VerifyCredentialJob`) so the HTTP request returns immediately
  and verification runs on the queue.
- **Swappable Veritas client**: the container binds the fake by default and an
  HTTP-backed client when `VERITAS_DRIVER=http`, so tests are deterministic and
  production talks to the real service.

## Layout

```
app/Domain/          ShareGrant, VerificationOutcome, VeritasClient (+ Fake)
app/Models/          User (roles), Employer, CredentialRequest, VerificationResult
app/Policies/        CredentialRequestPolicy
app/Jobs/            VerifyCredentialJob
app/Http/Controllers CredentialRequestController
database/migrations  employers, credential_requests, verification_results, users.role
database/factories   + seeders (demo data)
tests/Unit/          ShareGrantTest.php (Pest) + run_domain_tests.php (plain-PHP runner)
tests/Feature/       policy + verification-flow tests (Pest)
```

## Setup & test

This repo is the **application layer** for a Laravel 12 project.

```bash
composer install            # pulls Laravel 12 + Pest
cp .env.example .env && php artisan key:generate
php artisan migrate --seed  # sqlite by default
php artisan test            # Pest: domain, policy (403), verification flow, queue
php artisan serve
```

## Verification status

The full Pest suite was run for real on 24 Nov 2026 (`composer install` + `php artisan test` in a clean
Docker container): **12 passed (16 assertions)**, covering the domain, the policy (403) paths, the
verification flow and the queue. The plain-PHP domain runner (`php tests/Unit/run_domain_tests.php`)
reports **13/13**.

That first real run found two gaps the README had not predicted: the repo carried only the
application layer, so Laravel's base `users` migration and the `storage/` skeleton were missing,
and 7 of the 12 tests failed. Both are now in the repo
(`database/migrations/0001_01_01_000000_create_users_table.php`, `storage/`), and `phpunit.xml`
sets the standard in-memory test drivers.
