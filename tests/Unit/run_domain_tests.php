<?php
/**
 * Framework-independent domain tests, runnable with plain `php` (no Composer).
 * These cover the bug-prone business logic: scoped-grant expiry/scope checks and
 * the Veritas verification state machine. The Pest suite (tests/) mirrors these
 * once `composer install` is available.
 */
require __DIR__ . '/../../app/Domain/VerificationOutcome.php';
require __DIR__ . '/../../app/Domain/VeritasClient.php';
require __DIR__ . '/../../app/Domain/FakeVeritasClient.php';
require __DIR__ . '/../../app/Domain/ShareGrant.php';

use App\Domain\FakeVeritasClient;
use App\Domain\ShareGrant;
use App\Domain\VerificationOutcome;

$failures = 0;
$count = 0;
function check(string $name, bool $cond): void {
    global $failures, $count;
    $count++;
    if ($cond) { echo "  ✓ $name\n"; }
    else { echo "  ✗ $name\n"; $GLOBALS['failures']++; }
}

$NOW = 1_000_000;

// --- ShareGrant ---
$g = new ShareGrant(['degree', 'employment'], $NOW + 3600);
check('live grant permits an in-scope claim', $g->permits('degree', $NOW));
check('live grant denies an out-of-scope claim', !$g->permits('salary', $NOW));
check('expired grant denies everything', !(new ShareGrant(['degree'], $NOW))->permits('degree', $NOW));
check('expired detection at boundary', (new ShareGrant(['degree'], $NOW))->isExpired($NOW));
check('revoked grant denies', !(new ShareGrant(['degree'], $NOW + 3600, true))->permits('degree', $NOW));
check('permitsAll true when all in scope', $g->permitsAll(['degree', 'employment'], $NOW));
check('permitsAll false on over-scope request', !$g->permitsAll(['degree', 'salary'], $NOW));
check('permitsAll false on empty request', !$g->permitsAll([], $NOW));

// --- Veritas verification state machine ---
$claims = ['name' => 'Jane Doe', 'degree' => 'BSc Computer Science', 'year' => 2024];
$sig = FakeVeritasClient::sign($claims);
$veritas = new FakeVeritasClient();

$valid = $veritas->verify(['id' => 'cred-1', 'claims' => $claims, 'signature' => $sig]);
check('valid credential -> valid', $valid['outcome'] === VerificationOutcome::Valid->value);

$tampered = $claims; $tampered['degree'] = 'PhD Nonsense';
$bad = $veritas->verify(['id' => 'cred-1', 'claims' => $tampered, 'signature' => $sig]);
check('tampered credential -> invalid', $bad['outcome'] === VerificationOutcome::Invalid->value);

$malformed = $veritas->verify(['id' => 'cred-1']);
check('malformed credential -> error', $malformed['outcome'] === VerificationOutcome::Error->value);

$revokedClient = new FakeVeritasClient(['cred-9']);
$rev = $revokedClient->verify(['id' => 'cred-9', 'claims' => $claims, 'signature' => $sig]);
check('revoked credential -> revoked', $rev['outcome'] === VerificationOutcome::Revoked->value);

check('outcome.isAcceptable only for Valid',
    VerificationOutcome::Valid->isAcceptable() && !VerificationOutcome::Invalid->isAcceptable());

echo "\n$count checks, $failures failed\n";
exit($failures === 0 ? 0 : 1);
