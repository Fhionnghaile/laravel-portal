<?php

namespace App\Domain;

/**
 * Deterministic Veritas stand-in for tests and local demo.
 *
 * A credential is "valid" when its `signature` equals a canonical signature over
 * its claims (so flipping any claim — a tampered credential — fails). Specific
 * ids can be marked revoked.
 */
class FakeVeritasClient implements VeritasClient
{
    /** @param string[] $revoked credential ids treated as revoked */
    public function __construct(private array $revoked = []) {}

    public static function sign(array $claims): string
    {
        ksort($claims);
        return hash('sha256', 'veritas-demo|' . json_encode($claims));
    }

    public function verify(array $credential): array
    {
        $claims = $credential['claims'] ?? null;
        $sig = $credential['signature'] ?? null;
        if (!is_array($claims) || !is_string($sig)) {
            return ['outcome' => VerificationOutcome::Error->value, 'reason' => 'malformed'];
        }
        if (!hash_equals(self::sign($claims), $sig)) {
            return ['outcome' => VerificationOutcome::Invalid->value, 'reason' => 'signature mismatch'];
        }
        if (in_array($credential['id'] ?? '', $this->revoked, true)) {
            return ['outcome' => VerificationOutcome::Revoked->value, 'reason' => 'revoked'];
        }
        return ['outcome' => VerificationOutcome::Valid->value, 'claims' => $claims];
    }
}
