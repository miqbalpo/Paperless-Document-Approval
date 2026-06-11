<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use PDOException;
use Spatie\Permission\Models\Role;
use function PHPUnit\Framework\throwException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        return view('admin/master_data/user/index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin/master_data/user/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string|max:255',
                'role_id' => 'required|string'
            ]);

            $userId = auth()->id();

            if (!$userId) {
                return back()->withErrors(['error' => 'User not authenticated.'])
                    ->withInput();
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role->name);

            return redirect()->route('admin.master_data.user.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            Log::info($e);
            return back()->with(['error' => 'Failed to create user: ' . $e->getMessage()])
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with(['error' => "Error : " . $e->errors()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin/master_data/user/edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'edit_user_name' => 'required|string|max:255',
            'edit_user_email' => 'required|email|max:255|unique:users,email,' . $id,
            'edit_user_password' => 'nullable|string|min:8|max:255',
            'edit_user_role_id' => 'required|integer|exists:roles,id',
        ]);

        try {
            $loginUserId = auth()->id();
            if (!$loginUserId) {
                return back()->withErrors(['error' => 'User not authenticated.'])->withInput();
            }

            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Cek: kalau user yang sedang login adalah admin,
            // dan dia sedang mencoba mengubah role dirinya sendiri
            if ($user->id == $loginUserId && $user->hasRole('admin')) {
                $currentRoleId = $user->roles->first()->id;
                if ($currentRoleId != $request->edit_user_role_id) {
                    throw new Exception("You cannot change your own admin role to another role.");
                }
            }

            // Update data user
            $user->update([
                'name' => $request->edit_user_name,
                'email' => $request->edit_user_email,
                'password' => $request->filled('edit_user_password')
                    ? Hash::make($request->edit_user_password)
                    : $user->password,
            ]);

            // Update role
            $role = Role::findOrFail($request->edit_user_role_id);
            $user->syncRoles([$role->name]);

            DB::commit();

            return redirect()->route('admin.master_data.user.index')
                ->with('success', 'User updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', "Failed to update user: " . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $loginUser = Auth::user();

            if ($user->id == $loginUser->id) {
                throw new Exception("Cannot delete the currently logged-in user");
            }

            $user->delete();

            DB::commit();

            return redirect()->route('admin.master_data.user.index')
                ->with('success', 'User deleted successfully.');
        } catch (PDOException $e) {
            DB::rollBack();

            if ($e->getCode() == '23000') {
                return redirect()->back()
                    ->with('error', 'There are employees linked to this user, so deletion is not allowed.');
            }

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }
}