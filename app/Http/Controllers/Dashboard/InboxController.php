<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DocumentRoutingApproval;
use App\Models\UploadDocument;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->can('view all documents')) {
            $documentsData = UploadDocument::with('routingApprovals')->get();
        } elseif ($user->hasRole('approver')) {
            $documentsData = UploadDocument::whereHas('routingApprovals', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->with('routingApprovals')
                ->get();
        } else {
            $documentsData = UploadDocument::where('created_by', $user->id)
                ->with('routingApprovals')
                ->get();
        }

        $documentsCount = $documentsData->count();

        $approversCount = [];
        $approvedCount = [];
        $rejectedCount = [];
        $waitingCount = [];
        $documentStatuses = [];

        foreach ($documentsData as $document) {
            $docId = $document->id;
            $approvers = $document->routingApprovals;

            $approversCount[$docId] = $approvers->count();
            $approvedCount[$docId]  = $approvers->whereNotNull('approved_at')->count();
            $rejectedCount[$docId]  = $approvers->whereNotNull('rejected_at')->count();
            $waitingCount[$docId]   = $approversCount[$docId] - $approvedCount[$docId] - $rejectedCount[$docId];

            // Status dokumen
            if ($rejectedCount[$docId] > 0) {
                $documentStatuses[$docId] = 'rejected';
            } elseif ($approvedCount[$docId] === $approversCount[$docId] && $approversCount[$docId] > 0) {
                $documentStatuses[$docId] = 'approved';
            } else {
                $documentStatuses[$docId] = 'waiting';
            }
        }

        $approvedTotal = collect($documentStatuses)
            ->filter(fn($s) => $s === 'approved')
            ->count();

        $rejectedTotal = collect($documentStatuses)
            ->filter(fn($s) => $s === 'rejected')
            ->count();

        $waitingTotal = collect($documentStatuses)
            ->filter(fn($s) => $s === 'waiting')
            ->count();

        return view('dashboard.inbox', compact(
            'documentsData',
            'documentsCount',
            'approversCount',
            'approvedCount',
            'rejectedCount',
            'waitingCount',
            'documentStatuses',
            'approvedTotal',
            'waitingTotal',
            'rejectedTotal'
        ));
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
}
