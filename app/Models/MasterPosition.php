<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterPosition extends Model
{
    use SoftDeletes;
    protected $table = 'master_positions';
    protected $fillable = ['code', 'name', 'created_by'];
    public $timestamps = true;

    /**
     * Get the user who created this position.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all employees with this position.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(MasterEmployee::class, 'position_id')->withTrashed();
    }

    public function getNameAttribute($value)
    {
        if ($this->trashed()) {
            return $value . ' (Archived)';
        }
        
        return $value;
    }
}
