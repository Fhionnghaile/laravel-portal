<?php

namespace App\Jobs;

use App\Domain\VeritasClient;
use App\Domain\VerificationOutcome;
use App\Models\CredentialRequest;
use App\Models\VerificationResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyCredentialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public CredentialRequest $request,
        public array $credential,
    ) {}

    public function handle(VeritasClient $veritas): void
    {
        $now = time();
        // Enforce the scoped, time-limited grant before calling Veritas.
        $grant = $this->request->toGrant();
        if (!$grant->permitsAll($this->request->scopes ?? [], $now)) {
            $this->record(VerificationOutcome::Error->value, null, 'grant expired or out of scope');
            return;
        }

        $res = $veritas->verify($this->credential);
        $this->record($res['outcome'], $res['claims'] ?? null, $res['reason'] ?? null);

        if ($res['outcome'] === VerificationOutcome::Valid->value) {
            $this->request->update(['status' => CredentialRequest::STATUS_VERIFIED]);
        }
    }

    private function record(string $outcome, ?array $claims, ?string $reason): void
    {
        VerificationResult::updateOrCreate(
            ['credential_request_id' => $this->request->id],
            ['outcome' => $outcome, 'claims' => $claims, 'reason' => $reason, 'verified_at' => now()],
        );
    }
}
