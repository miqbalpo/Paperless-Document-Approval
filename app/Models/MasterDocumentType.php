<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDocumentType extends Model
{
    use SoftDeletes;
    protected $table = 'master_document_types';
    protected $fillable = ['code', 'name', 'created_by'];
    public $timestamps = true;

    /**
     * Get the user who created this document type.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all documents of this type.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(UploadDocument::class, 'document_type_id')->withTrashed();
    }

    public function getNameAttribute($value)
{
    if ($this->trashed()) {
        return $value . ' (Archived)';
    }
    
    return $value;
}
}
