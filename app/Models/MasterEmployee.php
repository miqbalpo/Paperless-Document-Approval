<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterEmployee extends Model
{
    use SoftDeletes;
    protected $table = 'master_employees';
    protected $fillable = ['id_card', 'name', 'departement_id', 'position_id', 'user_id', 'created_by'];
    public $timestamps = true;

    /**
     * Get the user who created this employee record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the departement this employee belongs to.
     */
    public function departement(): BelongsTo
    {
        return $this->belongsTo(MasterDepartement::class, 'departement_id')->withTrashed();
    }

    /**
     * Get the position of this employee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(MasterPosition::class, 'position_id')->withTrashed();
    }

    /**
     * Get the user account associated with this employee (one-to-one).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function getNameAttribute($value)
    {
        if ($this->trashed()) {
            return $value . ' (Archived)';
        }
        
        return $value;
    }
}
