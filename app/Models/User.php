<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected static function booted()
    {
        static::created(function ($user) {
            $user->assignRole('approver');
        });
    }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the employee record associated with this user (one-to-one).
     */
    public function employee(): HasOne
    {
        return $this->hasOne(MasterEmployee::class, 'user_id')->withTrashed();
    }

    /**
     * Get the signature associated with this user (one-to-one).
     */
    public function signature(): HasOne
    {
        return $this->hasOne(Signature::class, 'user_id')->withTrashed();
    }

    /**
     * Get all document routing approvals for this user.
     */
    public function documentRoutingApprovals(): HasMany
    {
        return $this->hasMany(DocumentRoutingApproval::class, 'user_id')->withTrashed();
    }

    /**
     * Get all documents created by this user.
     */
    public function createdDocuments(): HasMany
    {
        return $this->hasMany(UploadDocument::class, 'created_by')->withTrashed();
    }

    /**
     * Get all departements created by this user.
     */
    public function createdDepartements(): HasMany
    {
        return $this->hasMany(MasterDepartement::class, 'created_by')->withTrashed();
    }

    /**
     * Get all document types created by this user.
     */
    public function createdDocumentTypes(): HasMany
    {
        return $this->hasMany(MasterDocumentType::class, 'created_by')->withTrashed();
    }

    /**
     * Get all positions created by this user.
     */
    public function createdPositions(): HasMany
    {
        return $this->hasMany(MasterPosition::class, 'created_by')->withTrashed();
    }

    /**
     * Get all employees created by this user.
     */
    public function createdEmployees(): HasMany
    {
        return $this->hasMany(MasterEmployee::class, 'created_by')->withTrashed();
    }
    

    public function employeeDetail(): HasOne // Relasi one-to-one/one-to-many. Gunakan HasOne jika satu User hanya punya satu MasterEmployee.
    {
        return $this->hasOne(MasterEmployee::class, 'user_id', 'id')->withTrashed();
    }

    public function getNameAttribute($value)
    {
        if ($this->trashed()) {
            return $value . ' (Archived)';
        }
        
        return $value;
    }
}
