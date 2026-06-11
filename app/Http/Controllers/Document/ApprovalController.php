<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use DB;
use Exception;
use Illuminate\Http\Request;
use App\Models\DocumentRoutingApproval;
use App\Models\Signature;
use App\Models\UploadDocument;
use App\Models\User;
use App\Services\Approval\RequestApprovalMailerService;
use Dom\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $email, string $key)
    {
        // Find approvals by key and order by sort (ascending)
        $approvals = DocumentRoutingApproval::where('key', $key)
            ->orderBy('sort')
            ->with(['user', 'signature', 'user.employee.position'])
            ->get();

        if ($approvals->isEmpty()) {
            abort(404);
        }

        $documentData = UploadDocument::find($approvals->first()->document_id);

        // Determine previous approver info for the highest approved sort (if any)
        $lastApproved = $approvals->whereNotNull('approved_at')->last();
        $prevApproverId = $lastApproved?->user_id;
        $prevApproverName = $lastApproved?->user?->name ?? null;
        $prevSign = $prevApproverId ? Signature::where('user_id', $prevApproverId)->value('filename') : null;

        // The page may be accessed via public email link, so resolve name/email from param
        $targetUser = User::where('email', $email)->first();
        $name = $targetUser?->name ?? $email;

        return view('document.approval.index', compact([
            'documentData',
            'approvals',
            'prevApproverId',
            'prevApproverName',
            'prevSign',
            'email',
            'name',
            'key'
        ]));
    }

    public function approve(Request $request, string $email, string $key)
    {
        $approval = DocumentRoutingApproval::
            whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->where('key', $key)
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->firstOrFail();

        if (!$approval) {
            return redirect()->back()->withErrors(['error' => 'No pending approval found for you.']);
        }

        $approval->approved_at = now();
        $approval->save();

        // Find next approver (next sort) and send email
        $next = DocumentRoutingApproval::where('document_id', $approval->document_id)
            ->where('sort', '>', $approval->sort)
            ->orderBy('sort', 'asc')
            ->first();

        Log::info($next);

        if ($next) {
            // send email to next approver
            $mailer = app(RequestApprovalMailerService::class);
            try {
                $mailer->sendRequestApprovalMail($next);
                // mark email_sent_at if column exists
                if (array_key_exists('email_sent_at', $next->getAttributes())) {
                    $next->email_sent_at = now();
                    $next->save();
                }
            } catch (\Throwable $e) {
                // Log but continue
                Log::error('Failed to send approval email: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Document approved succesfully');
    }

    public function reject(Request $request, string $email, string $key)
    {
        // Find the approval entry for this key and the approver identified by email (same pattern as approve)
        $approval = DocumentRoutingApproval::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })
        ->where('key', $key)
        ->whereNull('rejected_at')
        ->whereNull('approved_at')
        ->firstOrFail();

        $approval->rejected_at = now();
        $approval->save();

        return redirect()->back()->with('success', 'Document rejected succesfully');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
    public function destroy(string $id)
    {
        //
    }

    public function sendApprovalEmail(Request $request, UploadDocument $document)
    {
        // Find first pending approver (not approved, not rejected)
        $next = DocumentRoutingApproval::where('document_id', $document->id)
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->orderBy('sort', 'asc')
            ->first();

        if (!$next) {
            return redirect()->back()->with('info', 'No pending approver found.');
        }

        $mailer = app(RequestApprovalMailerService::class);
        try {
            $mailer->sendRequestApprovalMail($next);
            if (array_key_exists('email_sent_at', $next->getAttributes())) {
                $next->email_sent_at = now();
                $next->save();
            }
            return redirect()->back()->with('success', 'Approval email sent to ' . $next->user->email);
        } catch (\Throwable $e) {
            \Log::error('Failed to send approval email: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to send approval email']);
        }
    }

    /**
     * Handle signature upload from approver and link to user/signature
     */
    public function uploadSignature(Request $request, string $email, string $key)
    {
        $request->validate([
            'signature' => 'required|file|mimes:png|max:10240',
        ]);

        $user = null;

        try {
            if ($email) {
                $user = User::where('email', $email)->firstOrFail();
            } else {
                return redirect()->back()->with('error', 'User not found');
            }

            $username = User::find($user->id)->name;
            $extension = $request->file('signature')->extension();
            $fileName = 'signature_' . $username . '.' . $extension;

            $request->file('signature')->storeAs(
                'private/signature',
                $fileName
            );

            DB::beginTransaction();
            // Save or update signature model
            $signature = Signature::updateOrCreate(
                ['user_id' => $user->id],
                ['filename' => $fileName]
            );

            // Link signature to any pending approvals for this user in this document (resolve document by key)
            $approval = DocumentRoutingApproval::where('key', $key)->first();
            if ($approval) {
                DocumentRoutingApproval::where('document_id', $approval->document_id)
                    ->where('user_id', $user->id)
                    ->update(['signature_id' => $signature->id]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Signature uploaded successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e);
            return redirect()->back()->with('error', 'Error ' . $e->getMessage());
        }
    }
    public function getSignatureFile(Request $request, string $email, string $key)
    {

        // Cari approval dengan user email dan key
        $approval = DocumentRoutingApproval::with('signature')
            ->whereHas('user.signature', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->where('key', $key)
            ->firstOrFail();

        // Ambil nama file signature
        $fileName = $approval->user->signature->filename;
        $filePath = "signature/{$fileName}";

        // Pastikan file ada
        if (!Storage::disk('private')->exists($filePath)) {
            abort(404, 'Signature file not found.');
        }

        // Return file dengan response aman
        return response()->file(Storage::disk('private')->path($filePath));

    }
    public function getPdfFile(Request $request, string $email, string $key)
    {

        $approval = DocumentRoutingApproval::with('document')
            ->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->where('key', $key)
            ->firstOrFail();

        $fileName = $approval->document->filename;
        $filePath = "documents/{$fileName}";

        // Pastikan file ada
        if (!Storage::disk('private')->exists($filePath)) {
            abort(404, 'PDF file not found.');
        }

        // Return file dengan response aman
        return response()->file(Storage::disk('private')->path($filePath));
    }
}
