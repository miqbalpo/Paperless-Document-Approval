<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadDocument extends Model
{
    use SoftDeletes;
    protected $table = 'upload_documents';
    protected $fillable = ['document_number', 'title', 'date', 'document_type_id', 'departement_id', 'filename', 'signature_position', 'status', 'created_by'];
    public $timestamps = true;

    /**
     * Get the user who created this document.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the document type of this document.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(MasterDocumentType::class, 'document_type_id')->withTrashed();
    }

    /**
     * Get the departement that owns this document.
     */
    public function departement(): BelongsTo
    {
        return $this->belongsTo(MasterDepartement::class, 'departement_id')->withTrashed();
    }

    /**
     * Get all routing approvals for this document.
     */
    public function routingApprovals(): HasMany
    {
        return $this->hasMany(DocumentRoutingApproval::class, 'document_id')->withTrashed();
    }

    
    public function getApprovalStatusAttribute()
    {
        // 1. Cek apakah dokumen dihapus/removed (jika status DB 'Removed')
        if ($this->status == 'Removed') {
            return 'Removed';
        }

        // Load relasi jika belum di-load (untuk mencegah error)
        $approvals = $this->routingApprovals; 

        // 2. Cek REJECTED: Jika ada satu saja yang reject
        if ($approvals->whereNotNull('rejected_at')->isNotEmpty()) {
            return 'Rejected';
        }

        // 3. Cek APPROVED: Jika jumlah approved == jumlah total approver
        // (Pastikan ada approver, jika 0 maka anggap Waiting atau Draft)
        if ($approvals->count() > 0 && $approvals->whereNotNull('approved_at')->count() == $approvals->count()) {
            return 'Approved';
        }

        // 4. Sisanya adalah WAITING (Active tapi belum full approve)
        return 'Waiting';
    }

    // Opsional: Accessor untuk warna badge biar View makin bersih
    public function getStatusColorAttribute()
    {
        return match ($this->approval_status) {
            'Approved' => 'bg-green-100 text-green-700',
            'Rejected' => 'bg-red-100 text-red-700',
            'Waiting'  => 'bg-yellow-100 text-yellow-800',
            'Removed'  => 'bg-gray-100 text-gray-800',
            default    => 'bg-blue-100 text-blue-800',
        };
    }

    public function getNameAttribute($value)
    {
        if ($this->trashed()) {
            return $value . ' (Archived)';
        }
        
        return $value;
    }
}
