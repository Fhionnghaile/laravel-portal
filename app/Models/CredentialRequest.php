<?php

namespace App\Models;

use App\Domain\ShareGrant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CredentialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id', 'candidate_email', 'scopes', 'status',
        'share_token', 'approved_at', 'expires_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    protected static function booted(): void
    {
        static::creating(function (CredentialRequest $r) {
            $r->share_token ??= Str::random(40);
            $r->status ??= self::STATUS_PENDING;
        });
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function result()
    {
        return $this->hasOne(VerificationResult::class);
    }

    /** Build the pure-domain grant this request represents. */
    public function toGrant(): ShareGrant
    {
        return new ShareGrant(
            scopes: $this->scopes ?? [],
            expiresAt: $this->expires_at?->getTimestamp() ?? 0,
            revoked: $this->status === self::STATUS_REJECTED,
        );
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_VERIFIED], true);
    }
}
