<?php

use App\Domain\FakeVeritasClient;
use App\Domain\ShareGrant;
use App\Domain\VerificationOutcome;

it('permits an in-scope claim while live', function () {
    $g = new ShareGrant(['degree'], time() + 3600);
    expect($g->permits('degree', time()))->toBeTrue();
});

it('denies an out-of-scope claim', function () {
    $g = new ShareGrant(['degree'], time() + 3600);
    expect($g->permits('salary', time()))->toBeFalse();
});

it('denies everything once expired', function () {
    $g = new ShareGrant(['degree'], time() - 1);
    expect($g->permits('degree', time()))->toBeFalse();
});

it('rejects over-scope requests', function () {
    $g = new ShareGrant(['degree'], time() + 3600);
    expect($g->permitsAll(['degree', 'salary'], time()))->toBeFalse();
});

it('verifies a valid credential and rejects a tampered one', function () {
    $claims = ['name' => 'Jane', 'degree' => 'BSc'];
    $sig = FakeVeritasClient::sign($claims);
    $veritas = new FakeVeritasClient();

    expect($veritas->verify(['id' => 'c1', 'claims' => $claims, 'signature' => $sig])['outcome'])
        ->toBe(VerificationOutcome::Valid->value);

    $tampered = $claims; $tampered['degree'] = 'PhD';
    expect($veritas->verify(['id' => 'c1', 'claims' => $tampered, 'signature' => $sig])['outcome'])
        ->toBe(VerificationOutcome::Invalid->value);
});
