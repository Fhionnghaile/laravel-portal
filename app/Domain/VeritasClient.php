<?php

namespace App\Domain;

/**
 * The Veritas verification port. The portal depends on this interface, so tests
 * inject a fake and production injects an HTTP-backed client.
 */
interface VeritasClient
{
    /**
     * @param array $credential decoded credential (id, claims, signature, ...)
     * @return array{outcome: string, claims?: array, reason?: string}
     */
    public function verify(array $credential): array;
}
