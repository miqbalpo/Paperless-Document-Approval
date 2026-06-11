<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterDepartement;
use App\Models\MasterEmployee;
use App\Models\MasterPosition;
use Illuminate\Http\Request;


class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = MasterEmployee::all();
        $departements = MasterDepartement::all();
        $positions = MasterPosition::all();

        return view('admin/master_data/employee/index', compact(['employees', 'departements', 'positions']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('admin/master_data/employee/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'emp_id' => 'string|min:1|required',
            'emp_name' => 'string|min:1|required',
            'dept_id' => 'required',
            'pos_id' => 'required'
        ]);


        $add_employee = MasterEmployee::updateOrCreate([
                'id_card' => $request->emp_id,
                'created_by' => auth()->id(),
                'name' => $request->emp_name,
                'departement_id' => $request->dept_id,
                'position_id' => $request->pos_id,
                // 'user_id' => MasterEmployee::max('user_id') + 1
            ]
        );

        return redirect()->route('admin.master_data.employee.index')
        ->with('success', 'Employee saved successfully!')
        ->with('employee', $add_employee);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin/master_data/employee/edit');
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, $id)
{
    $request->validate([
        'emp_id' => 'required|string|min:1',
        'emp_name' => 'required|string|min:1',
        'dept_id' => 'required',
        'pos_id' => 'required',
    ]);

    $employee = MasterEmployee::findOrFail($id);

    $employee->update([
        'id_card' => $request->emp_id,
        'name' => $request->emp_name,
        'departement_id' => $request->dept_id,
        'position_id' => $request->pos_id,
    ]);

    // Add success message
    return redirect()->back()->with('success', 'Employee updated successfully!');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        MasterEmployee::where('id_card', $id)->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Employee deleted']);
        }

        return redirect()->back()->with('success', 'Employee deleted successfully!');
    }
}
