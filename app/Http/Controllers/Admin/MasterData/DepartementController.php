<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterDepartement;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dpt = MasterDepartement::all();
        return view('admin/master_data/departement/index', compact('dpt'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin/master_data/departement/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'department_code' => 'required|string|max:10,code',
                'department_name' => 'required|string|max:255',
            ]);
            // Ambil ID user yang sedang login
            $userId = auth()->id(); 
            
            // Pastikan ada user yang login sebelum melanjutkan (Opsional tapi dianjurkan)
            if (!$userId) {
                return back()->withErrors(['error' => 'User not authenticated.'])
                    ->withInput();
            }
            // Simpan data departemen ke database
            MasterDepartement::create([
                'code' => $request->department_code,
                'name' => $request->department_name,
                'created_by' => $userId,
            ]);

            return redirect()->route('admin.master_data.departement.index')
                ->with('success', 'Departement created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create departement: ' . $e->getMessage()])
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin/master_data/departement/edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'code' => 'required|string|max:10,code',
            'name' => 'required|string|max:255',
        ]);

        try {
            // // Ambil ID user yang sedang login
            // $userId = auth()->id(); 
            
            // // Pastikan ada user yang login sebelum melanjutkan (Opsional tapi dianjurkan)
            // if (!$userId) {
            //     return back()->withErrors(['error' => 'User not authenticated.'])
            //         ->withInput();
            // }
            // Update data departemen di database
            $departement = MasterDepartement::findOrFail($id);
            $departement->update([
                'code' => $request->code,
                'name' => $request->name,
                // 'created_by' => $userId,
            ]);

            return redirect()->route('admin.master_data.departement.index')
                ->with('success', 'Departement updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update departement: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Hapus data departemen dari database
            $departement = MasterDepartement::findOrFail($id);
            $departement->delete();

            return redirect()->route('admin.master_data.departement.index')
                ->with('success', 'Departement deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete departement: ' . $e->getMessage()]);
        }
    }
}
