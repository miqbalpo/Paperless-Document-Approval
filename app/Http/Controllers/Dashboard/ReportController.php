<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadDocument;
use App\Models\DocumentRoutingApproval;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $documents = UploadDocument::with([
            'routingApprovals.user.employeeDetail.position',
            'createdBy',
            'documentType',
            'departement'
        ]);

        if ($user->hasRole('admin') || $user->can('view all documents')) {
            // Tidak ada batasan
        } elseif ($user->hasRole('approver')) {
            $documents->whereHas('routingApprovals', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } else {
            $documents->where('created_by', $user->id);
        }

        $documents->unless($request->status === 'Removed', function ($query) {
            $query->where('status', '!=', 'Removed');
        });


        $documents->when($request->documentType, function ($q, $type) {
            $q->whereHas('documentType', function ($subQ) use ($type) {
                $subQ->where('name', $type);
            });
        });


        $documents->when($request->date, function ($q, $date) {
            $q->whereDate('date', $date);
        });

        $documents->when($request->status, function ($query, $status) {

            if ($status === 'Approved') {
                $query->has('routingApprovals')
                    ->whereDoesntHave('routingApprovals', fn($q) => $q->whereNull('approved_at'))
                    ->whereDoesntHave('routingApprovals', fn($q) => $q->whereNotNull('rejected_at'));
            } elseif ($status === 'Rejected') {
                $query->whereHas(
                    'routingApprovals',
                    fn($q) =>
                    $q->whereNotNull('rejected_at')
                );
            } elseif ($status === 'Waiting') {
                $query->whereHas(
                    'routingApprovals',
                    fn($q) =>
                    $q->whereNull('approved_at')
                )->whereDoesntHave(
                    'routingApprovals',
                    fn($q) =>
                    $q->whereNotNull('rejected_at')
                );
            } elseif ($status === 'Removed') {
                $query->where('status', 'Removed');
            } else {
                $query->where('status', $status);
            }
        });

        $finalDocuments = $documents
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $countQuery = UploadDocument::query();

        if ($user->hasRole('admin') || $user->can('view all documents')) {
        } elseif ($user->hasRole('approver')) {
            $countQuery->whereHas('routingApprovals', fn($q) => $q->where('user_id', $user->id));
        } else {
            $countQuery->where('created_by', $user->id);
        }

        $countTotal = $countQuery->where('status', '!=', 'Removed')->count();

        $countRemoved = $countQuery->clone()->where('status', 'Removed')->count();

        $countRejected = $countQuery->clone()
            ->where('status', '!=', 'Removed')
            ->whereHas('routingApprovals', fn($q) => $q->whereNotNull('rejected_at'))
            ->count();

        $countApproved = $countQuery->clone()
            ->where('status', '!=', 'Removed')
            ->has('routingApprovals')
            ->whereDoesntHave('routingApprovals', fn($q) => $q->whereNull('approved_at'))
            ->whereDoesntHave('routingApprovals', fn($q) => $q->whereNotNull('rejected_at'))
            ->count();

        $countWaiting = $countQuery->clone()
            ->where('status', '!=', 'Removed')
            ->whereHas('routingApprovals', fn($q) => $q->whereNull('approved_at'))
            ->whereDoesntHave('routingApprovals', fn($q) => $q->whereNotNull('rejected_at'))
            ->count();

        $allTypes = UploadDocument::with('documentType')
            ->get()
            ->pluck('documentType.name')
            ->unique();

        $dbStatuses = UploadDocument::pluck('status')->unique()->toArray();
        $logicStatuses = ['Approved', 'Rejected', 'Waiting'];
        $allStatuses = array_unique(array_merge($logicStatuses, $dbStatuses));

        return view('dashboard.report', [
            'documents'     => $finalDocuments,
            'allTypes'      => $allTypes,
            'allStatuses'   => $allStatuses,
            'countTotal'    => $countTotal,
            'countWaiting'  => $countWaiting,
            'countApproved' => $countApproved,
            'countRejected' => $countRejected,
            'countRemoved'  => $countRemoved,
        ]);
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

    public function export(Request $request)
    {
        // TODO: Gunakan querystring dari request untuk memfilter data (misal status dari dokument)
    }
}
