<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentRoutingApproval extends Model
{
    use SoftDeletes;
    protected $table = 'document_routing_approvals';
    protected $fillable = ['document_id', 'key', 'sort', 'user_id', 'approved_at', 'rejected_at', 'signature_id', 'email_sent_at', 'signature_position'];

    public $timestamps = true;

    /**
     * Get the document being routed for approval.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(UploadDocument::class, 'document_id')->withTrashed();
    }

    /**
     * Get the user who should approve this document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * Get the signature used for approval.
     */
    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class, 'signature_id')->withTrashed();
    }

    public function getNameAttribute($value)
    {
        if ($this->trashed()) {
            return $value . ' (Archived)';
        }
        
        return $value;
    }
}
