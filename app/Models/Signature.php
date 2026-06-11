<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signature extends Model
{
    use SoftDeletes;
    protected $table = 'signatures';
    protected $fillable = ['filename', 'user_id'];
    public $timestamps = true;

    /**
     * Get the user who owns this signature (one-to-one).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * Get all document routing approvals using this signature.
     */
    public function documentRoutingApprovals(): HasMany
    {
        return $this->hasMany(DocumentRoutingApproval::class, 'signature_id')->withTrashed();
    }

    public function getNameAttribute($value)
    {
        if ($this->trashed()) {
            return $value . ' (Archived)';
        }
        
        return $value;
    }
}
