<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterPosition;
use Illuminate\Http\Request;
use Log;
use PDOException;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = MasterPosition::all();
        return view('admin/master_data/position/index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin/master_data/position/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'position_code' => 'required|string|max:10',
                'position_name' => 'required|string|max:255',
            ]);

            $userId = auth()->id();

            if (!$userId) {
                return back()->withErrors(['error' => 'User not authenticated.'])
                    ->withInput();
            }
            // Simpan data departemen ke database
            MasterPosition::create([
                'code' => $request->position_code,
                'name' => $request->position_name,
                'created_by' => $userId,
            ]);

            return redirect()->route('admin.master_data.position.index')
                ->with('success', 'Position created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create positon: ' . $e->getMessage()])
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors(['error' => $e->errors()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin/master_data/position/edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'edit_position_code' => 'required|string|max:10',
            'edit_position_name' => 'required|string|max:255',
        ]);

        try {
            $userId = auth()->id();

            if (!$userId) {
                return back()->withErrors(['error' => 'User not authenticated.'])
                    ->withInput();
            }

            $position = MasterPosition::findOrFail($id);
            $position->update([
                'code' => $request->edit_position_code,
                'name' => $request->edit_position_name,
            ]);

            return redirect()->route('admin.master_data.position.index')
                ->with('success', 'Position updated successfully.');
        } catch (\Exception $e) {
            return back()->with(['error' => 'Failed to update position: ' . $e->getMessage()])
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with(['error' => $e->errors()])
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
            $position = MasterPosition::findOrFail($id);
            $position->delete();

            return redirect()->route('admin.master_data.position.index')
                ->with('success', 'Position deleted successfully.');
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->back()->with('error', 'There are "Employees" who have this position');
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }

    }
}