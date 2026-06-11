<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterDocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = MasterDocumentType::all();

        return view('admin/master_data/document_type/index', compact(['documents']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin/master_data/document_type/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doc_code' => 'string|min:1|required',
            'doc_name' => 'string|min:1|required',
        ]);


        $add_document = MasterDocumentType::updateOrCreate(
            [
                'created_by' => auth()->id(),
                'code' => $request->doc_code,
                'name' => $request->doc_name
            ]
        );

        return redirect()->route('admin.master_data.document_type.index')
            ->with('success', 'Document saved successfully!')
            ->with('employee', $add_document);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin/master_data/document_type/edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'doc_code' => 'required|string|min:1',
            'doc_name' => 'required|string|min:1',
        ]);

        $document = MasterDocumentType::findOrFail($id);

        $document->update([
            'code' => $request->doc_code,
            'name' => $request->doc_name,
        ]);

        // Add success message
        return redirect()->back()->with('success', 'Document updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        MasterDocumentType::where('code', $id)->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Document Type deleted']);
        }

        return redirect()->back()->with('success', 'Document type deleted successfully!');
    }
}
