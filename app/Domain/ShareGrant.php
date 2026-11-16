<?php

namespace App\Domain;

/**
 * A candidate's scoped, time-limited consent for an employer to verify a
 * credential. Pure value logic — no framework — so it is unit-tested directly.
 */
final class ShareGrant
{
    /**
     * @param string[] $scopes granted claim scopes, e.g. ['degree','employment']
     */
    public function __construct(
        public readonly array $scopes,
        public readonly int $expiresAt,   // unix timestamp
        public readonly bool $revoked = false,
    ) {}

    public function isExpired(int $now): bool
    {
        return $now >= $this->expiresAt;
    }

    /** True only if the grant is live and actually covers the requested scope. */
    public function permits(string $scope, int $now): bool
    {
        if ($this->revoked || $this->isExpired($now)) {
            return false;
        }
        return in_array($scope, $this->scopes, true);
    }

    /** Reject a request that asks for more than was granted. */
    public function permitsAll(array $requestedScopes, int $now): bool
    {
        foreach ($requestedScopes as $s) {
            if (!$this->permits($s, $now)) {
                return false;
            }
        }
        return $requestedScopes !== [];
    }
}
