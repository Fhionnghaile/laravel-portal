<?php

namespace App\Policies;

use App\Models\CredentialRequest;
use App\Models\User;

class CredentialRequestPolicy
{
    /** Only employers (or admins) may create verification requests. */
    public function create(User $user): bool
    {
        return $user->isEmployer() || $user->isAdmin();
    }

    /** An employer may view only their own requests; admins see all. */
    public function view(User $user, CredentialRequest $request): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $user->employer && $user->employer->id === $request->employer_id;
    }

    public function update(User $user, CredentialRequest $request): bool
    {
        return $this->view($user, $request);
    }
}
