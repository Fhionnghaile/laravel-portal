<?php

namespace App\Domain;

/** The result of verifying a credential against Veritas. */
enum VerificationOutcome: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';     // signature mismatch / tampered
    case Revoked = 'revoked';     // signature ok but credential revoked
    case Expired = 'expired';     // credential's own validity window passed
    case Error = 'error';         // could not verify (transport, malformed)

    public function isAcceptable(): bool
    {
        return $this === self::Valid;
    }
}
