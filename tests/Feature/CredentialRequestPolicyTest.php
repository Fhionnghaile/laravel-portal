<?php

use App\Models\CredentialRequest;
use App\Models\Employer;
use App\Models\User;

it('lets an employer create a request', function () {
    $user = User::factory()->create(['role' => 'employer']);
    Employer::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('requests.store'), [
            'candidate_email' => 'cand@example.com',
            'scopes' => ['degree'],
            'ttl_hours' => 24,
        ])
        ->assertRedirect();

    expect(CredentialRequest::count())->toBe(1);
});

it('forbids a non-employer from creating a request (403)', function () {
    $user = User::factory()->candidate()->create();

    $this->actingAs($user)
        ->post(route('requests.store'), [
            'candidate_email' => 'cand@example.com',
            'scopes' => ['degree'],
            'ttl_hours' => 24,
        ])
        ->assertForbidden();
});

it('forbids viewing another employer’s request', function () {
    $mine = Employer::factory()->create();
    $other = Employer::factory()->create();
    $req = CredentialRequest::factory()->create(['employer_id' => $other->id]);

    $this->actingAs($mine->user)
        ->get(route('requests.show', $req))
        ->assertForbidden();
});
