<?php

use App\Domain\FakeVeritasClient;
use App\Domain\VeritasClient;
use App\Jobs\VerifyCredentialJob;
use App\Models\CredentialRequest;
use App\Models\Employer;
use Illuminate\Support\Facades\Bus;

it('dispatches the verification job when a candidate approves', function () {
    Bus::fake();
    $employer = Employer::factory()->create();
    $req = CredentialRequest::factory()->create(['employer_id' => $employer->id]);

    $this->actingAs($employer->user)
        ->post(route('requests.verify', $req), ['credential' => ['id' => 'c1']])
        ->assertRedirect();

    Bus::assertDispatched(VerifyCredentialJob::class);
});

it('records a valid outcome for a good credential (mocked Veritas)', function () {
    $this->app->bind(VeritasClient::class, fn () => new FakeVeritasClient());
    $employer = Employer::factory()->create();
    $req = CredentialRequest::factory()->create([
        'employer_id' => $employer->id,
        'scopes' => ['degree'],
        'expires_at' => now()->addDay(),
    ]);

    $claims = ['degree' => 'BSc'];
    $credential = ['id' => 'c1', 'claims' => $claims, 'signature' => FakeVeritasClient::sign($claims)];
    (new VerifyCredentialJob($req, $credential))->handle(new FakeVeritasClient());

    expect($req->fresh()->result->outcome)->toBe('valid')
        ->and($req->fresh()->status)->toBe(CredentialRequest::STATUS_VERIFIED);
});

it('records invalid for a tampered credential', function () {
    $employer = Employer::factory()->create();
    $req = CredentialRequest::factory()->create([
        'employer_id' => $employer->id, 'scopes' => ['degree'], 'expires_at' => now()->addDay(),
    ]);
    $good = FakeVeritasClient::sign(['degree' => 'BSc']);
    $credential = ['id' => 'c1', 'claims' => ['degree' => 'PhD'], 'signature' => $good];
    (new VerifyCredentialJob($req, $credential))->handle(new FakeVeritasClient());

    expect($req->fresh()->result->outcome)->toBe('invalid');
});

it('rejects verification under an expired grant', function () {
    $employer = Employer::factory()->create();
    $req = CredentialRequest::factory()->expired()->create([
        'employer_id' => $employer->id, 'scopes' => ['degree'],
    ]);
    $claims = ['degree' => 'BSc'];
    $credential = ['id' => 'c1', 'claims' => $claims, 'signature' => FakeVeritasClient::sign($claims)];
    (new VerifyCredentialJob($req, $credential))->handle(new FakeVeritasClient());

    expect($req->fresh()->result->outcome)->toBe('error');
});
