<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDepartement extends Model
{
    use SoftDeletes;
    protected $table = 'master_departements';
    protected $fillable = ['code', 'name', 'created_by'];
    public $timestamps = true;

    /**
     * Get the user who created this departement.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all employees in this departement.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(MasterEmployee::class, 'departement_id')->withTrashed();
    }

    /**
     * Get all documents for this departement.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(UploadDocument::class, 'departement_id')->withTrashed();
    }

    // Pastikan tetap pakai use SoftDeletes di atas
    public function getNameAttribute($value)
    {
        if ($this->trashed()) {
            return $value . ' (Archived)';
        }
        
        return $value;
    }
}
