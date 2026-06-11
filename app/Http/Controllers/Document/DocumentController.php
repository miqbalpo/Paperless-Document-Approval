<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\UploadDocument;
use Illuminate\Http\Request;
use Log;
use App\Models\Signature;
use App\Models\User;
use App\Models\MasterDepartement;
use App\Models\MasterDocumentType;
use App\Models\DocumentRoutingApproval;
use Dom\DocumentType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;


class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $user = auth()->user();

    $documents = UploadDocument::with([
        'routingApprovals' => function ($q) {
            $q->orderBy('sort')
              ->with(['user.employee.position']);
        },
        'createdBy',
        'documentType',
        'departement'
    ])->where('status', '!=', 'Removed');

    if ($user->hasRole('admin') || $user->can('view all documents')) {
        // no restriction
    }
    elseif ($user->hasRole('approver')) {
        $documents->whereHas('routingApprovals', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }
    else {
        $documents->where('created_by', $user->id);
    }

    $documents->when($request->status, function ($query, $status) {

        if ($status === 'Approved') {
            $query->has('routingApprovals')
                ->whereDoesntHave('routingApprovals', fn ($q) =>
                    $q->whereNull('approved_at')
                )
                ->whereDoesntHave('routingApprovals', fn ($q) =>
                    $q->whereNotNull('rejected_at')
                );
        }

        elseif ($status === 'Rejected') {
            $query->whereHas('routingApprovals', fn ($q) =>
                $q->whereNotNull('rejected_at')
            );
        }

        elseif ($status === 'Waiting') {
            $query->whereHas('routingApprovals', fn ($q) =>
                $q->whereNull('approved_at')
            )
            ->whereDoesntHave('routingApprovals', fn ($q) =>
                $q->whereNotNull('rejected_at')
            );
        }

        else {
            // Status bawaan database (Active, Draft, dll)
            $query->where('status', $status);
        }
    });

    $finalDocuments = $documents
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->withQueryString();


    $dbStatuses = UploadDocument::pluck('status')->unique()->toArray();
    $logicStatuses = ['Approved', 'Rejected', 'Waiting'];

    // Hilangkan "Removed" dari dropdown
    if (($key = array_search('Removed', $dbStatuses)) !== false) {
        unset($dbStatuses[$key]);
    }

    $allStatuses = array_unique(array_merge($logicStatuses, $dbStatuses));

    return view('document.index', [
        'documents'   => $finalDocuments,
        'allStatuses' => $allStatuses,
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $approvers = User::all();
        $dokumentTypes = MasterDocumentType::all();
        $departments = MasterDepartement::all();
        return view('document.create', compact('approvers', 'dokumentTypes', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Isi tabel 'upload_documents', 'document_routing_approvals'
            // 'document_routing_approvals.key' isi string random 16 karakter(angka&huruf)
            $request->validate([
                'document_number' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'document_date' => 'required|date',
                'document_type_id' => 'required|integer',
                'department_id' => 'required|integer',
                // 'status' => 'required|string|max:50',
                'file' => 'required|file|mimes:pdf|max:2048',
            ]);
            // Simpan file ke storage
            $fileName = $request->title . '.' . $request->file('file')->getClientOriginalExtension();

            $filePath = $request->file('file')->storeAs('documents', $fileName, 'private');
            // Simpan data ke tabel upload_documents
            $document = UploadDocument::create([
                'document_number' => $request->document_number,
                'title' => $request->title,
                'date' => $request->document_date,
                'document_type_id' => $request->document_type_id,
                'departement_id' => $request->department_id,
                'filename' => $fileName,
                'status' => 'Active',
                'created_by' => auth()->id(),
            ]);
            // dd($document);
            $approverJson = $request->input('approver_order');
            // 2. Ubah JSON String menjadi Array PHP
            // Parameter 'true' berarti kita mengubahnya menjadi associative array, bukan object
            $approvers = json_decode($approverJson, true);

            // 3. Cek apakah datanya ada dan valid
            if (is_array($approvers) && count($approvers) > 0) {

                foreach ($approvers as $approver) {

                    // 1. CARI Tanda Tangan
                    // Menggunakan 'first()' untuk mendapatkan Model atau NULL
                    $signature = Signature::where('user_id', $approver['user_id'])->first();

                    // 2. Tentukan Nilai signature_id
                    // Jika $signature ditemukan (bukan NULL), ambil ID-nya.
                    // Jika tidak ditemukan, atur menjadi NULL.
                    $signatureId = $signature ? $signature->id : null;

                    // --- LOGIKA UTAMA: Data Selalu Masuk ---

                    DocumentRoutingApproval::create([
                        'document_id' => $document->id,
                        'user_id' => $approver['user_id'],
                        'sort' => $approver['order'],
                        'signature_id' => $signatureId, // Menggunakan variabel yang sudah dikondisikan
                        'key' => Str::random(16),
                    ]);

                    // Di sini, semua approver akan dimasukkan.
                    // Jika signature ada, signature_id terisi. Jika tidak ada, signature_id NULL.
                }
            }
            return redirect()->route('document.index')->with('success', 'Dokumen berhasil diunggah dan disimpan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors(['msg' => 'Validasi data gagal: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Terjadi kesalahan saat menyimpan dokumen. Silakan coba lagi.']);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('document.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('document.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // Cari Dokumen. findOrFail akan melempar 404 jika ID tidak ditemukan.
        $document = UploadDocument::findOrFail($id);
        // dd($document->id, $id);
        try {
            DB::transaction(function () use ($document) {

                // 1. Update Status Dokumen menjadi 'REMOVED'
                $document->status = 'removed';
                $document->save();

                // 3. Update Log Approval terkait (Opsional, tapi disarankan untuk konsistensi)
                // Semua langkah approval yang masih PENDING harus dibatalkan (CANCELED).
                // $document->approvalLogs()
                //         ->where('status', 'PENDING')
                //         ->update([
                //             'status' => 'CANCELED',
                //             'action_date' => now() // Tambahkan timestamp pembatalan
                //         ]);

            });

            return redirect()->route('document.index')->with('success', 'Dokumen berhasil diubah dihapus.');

        } catch (\Exception $e) {
            // Tangkap kegagalan database atau exception lainnya
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem saat membatalkan dokumen. Silakan coba lagi.']);
        }
    }

    public function getPdfFile(String $id){

        $pdfDocument = UploadDocument::findOrFail($id);
        $fileName = $pdfDocument->filename;

        // Path Manual ke file yang diharapkan
        $expectedPath = storage_path('app/private/documents/' . $fileName);

        // Path Fallback (misal: path file Doc2.pdf, atau NULL)
        $fallbackPath = public_path('Doc2.pdf');

        // --- Penggunaan Ternary Operator ---
        $file = file_exists($expectedPath)
                ? $expectedPath      // Jika file ADA, gunakan path file dokumen
                : $fallbackPath;     // Jika file TIDAK ADA, gunakan path fallback

        return response()->file($file);
    }

    public function getSignedPdfFile(UploadDocument $document)
    {
        $pdf = new Fpdi(); // extend dari FPDF

        $pageCount = $pdf->setSourceFile(storage_path('app/private/documents/' . $document->filename));

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId); // ambil ukuran halaman PDF (mm)
            $pdf->useTemplate($tplId);

            $approvals = $document->routingApprovals;
            foreach ($approvals as $approval) {
                if ($approval->approved_at == null || $approval->rejected_at != null) {
                    abort(403, "Approver has not approved this document");
                }
                if ($approval->signature_position == null) {
                    abort(403, "Signature position is not set");
                }

                $signaturePositions = json_decode($approval->signature_position, true);
                $signatureFilename = $approval->user->signature->filename;
                $signatureFile = storage_path('app/private/signature/' . $signatureFilename);

                // Pastikan file signature ada
                if (!file_exists($signatureFile)) {
                    Log::warning('Signature file not found: ' . $signatureFile);
                    continue;
                }

                foreach ($signaturePositions as $signaturePosition) {
                    if ($signaturePosition['page'] !== $pageNo) {
                        continue;
                    }

                    // Koordinat dari frontend disimpan dengan scale 1.5, jadi bagi dulu dengan 1.5 untuk dapat koordinat asli
                    $scale = 1.5; // scale yang digunakan di frontend
                    $dpi = 72; // DPI default FPDF
                    $mmPerInch = 25.4;

                    // Konversi dari pixel (dengan scale) ke mm
                    $xAxis = ($signaturePosition['x'] / $scale) * ($mmPerInch / $dpi);
                    $yAxis = ($signaturePosition['y'] / $scale) * ($mmPerInch / $dpi);
                    // Width = 0 untuk menjaga rasio asli gambar (tidak ketarik)
                    $width = 0;
                    // Height disesuaikan dengan nilai dari JSON
                    $height = ($signaturePosition['height'] / $scale) * ($mmPerInch / $dpi);

                    // Validasi koordinat agar tidak negatif dan dalam batas halaman
                    // Karena width = 0 (auto), kita asumsikan width gambar tidak akan melebihi halaman
                    $xAxis = max(0, $xAxis);
                    $yAxis = max(0, min($yAxis, $size['height'] - $height));
                    $pdf->Image($signatureFile, $xAxis, $yAxis, $width, $height);
                }
            }
        }

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="hasil.pdf"');
    }



}
