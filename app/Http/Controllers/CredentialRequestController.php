<?php

namespace App\Http\Controllers;

use App\Jobs\VerifyCredentialJob;
use App\Models\CredentialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CredentialRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', CredentialRequest::class);
        $employer = Auth::user()->employer;
        return view('requests.index', [
            'requests' => $employer ? $employer->credentialRequests()->latest()->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CredentialRequest::class);

        $data = $request->validate([
            'candidate_email' => ['required', 'email'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['string'],
            'ttl_hours' => ['required', 'integer', 'min:1', 'max:168'],
        ]);

        $cr = Auth::user()->employer->credentialRequests()->create([
            'candidate_email' => $data['candidate_email'],
            'scopes' => $data['scopes'],
            'expires_at' => now()->addHours($data['ttl_hours']),
        ]);

        return redirect()->route('requests.show', $cr)->with('status', 'Request created.');
    }

    public function show(CredentialRequest $credentialRequest)
    {
        $this->authorize('view', $credentialRequest);
        return view('requests.show', ['request' => $credentialRequest->load('result')]);
    }

    /** The candidate approves (scoped, time-limited) and the portal verifies. */
    public function verify(Request $request, CredentialRequest $credentialRequest)
    {
        $this->authorize('update', $credentialRequest);

        $credentialRequest->update([
            'status' => CredentialRequest::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        VerifyCredentialJob::dispatch($credentialRequest, $request->input('credential', []));

        return redirect()->route('requests.show', $credentialRequest)
            ->with('status', 'Verification queued.');
    }
}
