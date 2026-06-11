<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentRoutingApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositioningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request ,DocumentRoutingApproval $documentRoutingApproval)
    {
        return view('document.positioning.index', compact('documentRoutingApproval'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DocumentRoutingApproval $documentRoutingApproval)
    {
        $positions = $request->positions;

        try {
            DB::beginTransaction();
            // Store as JSON string if it's an array
            $documentRoutingApproval->update(['signature_position' => json_encode($positions)]);
            DB::commit();
            return response()->json(['message' => 'Positions stored successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing positions: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to store positions.'], 500);
        }

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
