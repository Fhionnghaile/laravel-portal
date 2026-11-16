<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationResult extends Model
{
    use HasFactory;

    protected $fillable = ['credential_request_id', 'outcome', 'claims', 'reason', 'verified_at'];
    protected $casts = ['claims' => 'array', 'verified_at' => 'datetime'];

    public function credentialRequest()
    {
        return $this->belongsTo(CredentialRequest::class);
    }
}
