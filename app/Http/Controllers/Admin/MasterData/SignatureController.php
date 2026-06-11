<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SignatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $signatures = Signature::with('user')->get();
        $user = User::all();
        return view('admin/master_data/signature/index', compact('signatures', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin/master_data/signature/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO : Save to storage/app/private/signature
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id|unique:signatures,user_id', // Pastikan user ada dan belum punya tanda tangan
                'filename' => 'required|file|image|max:2048', // Pastikan file adalah gambar, max 2MB
            ]);

            $userId = $request->user_id;
            $username = User::find($userId)->name;
            $extension = $request->file('filename')->extension();
            $fileName = 'signature_' . $username . '.' . $extension;
            // Menyimpan file dan mendapatkan nama file yang dihasilkan oleh Laravel
            $request->file('filename')->storeAs(
                'private/signature', 
                $fileName
            );

            // 3. Menyimpan data ke database
            Signature::create([
                'user_id' => $userId,
                'filename' => $fileName,
            ]);

            return redirect()->route('admin.master_data.signature.index')->with('success', 'Signature uploaded and saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload signature: ' . $e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin/master_data/signature/edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Ambil data Signature yang akan diupdate
        $signature = Signature::findOrFail($id);
        $oldFileName = $signature->filename;

        // 2. Validasi Input
        $request->validate([
            // user_id harus unik, tetapi harus mengabaikan record saat ini ($id)
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('signatures', 'user_id')->ignore($id),
            ],
            // File 'signature' tidak wajib diisi. Jika diisi, validasinya berjalan.
            'signature' => 'nullable|file|image|max:2048', 
        ]);

        // 3. Persiapan Data Update
        $dataToUpdate = [
            'user_id' => $request->user_id,
        ];

        try {
            if ($request->hasFile('filename')) {
                // **A. Proses Update File**

                $userId = $request->user_id;
                $username = User::find($userId)->name;
                $extension = $request->file('filename')->extension();
                $newFileName = 'signature_' . $username . '.' . $extension;
                
                // 4. Hapus File Lama dari Storage
                if (Storage::disk('local')->exists('private/signature/' . $oldFileName)) {
                    Storage::disk('local')->delete('private/signature/' . $oldFileName);
                }

                // 5. Simpan File Baru ke Storage
                $request->file('filename')->storeAs(
                    'private/signature', 
                    $newFileName
                );

                // Tambahkan nama file baru ke array update
                $dataToUpdate['filename'] = $newFileName;
                
            } elseif ($request->user_id != $signature->user_id) {
                // **B. Jika User ID Berubah, tapi File TIDAK Diubah**
                // Kita perlu merename file lama di storage agar sesuai dengan format $user_id.extension

                $oldExtension = pathinfo($oldFileName, PATHINFO_EXTENSION);
                $newFileName = $request->user_id . '.' . $oldExtension;

                if (Storage::disk('local')->exists('private/signature/' . $oldFileName)) {
                    // Merename (memindahkan) file di storage
                    Storage::disk('local')->move(
                        'private/signature/' . $oldFileName,
                        'private/signature/' . $newFileName
                    );
                }
                // Update nama file di database agar sesuai dengan format baru
                $dataToUpdate['filename'] = $newFileName;
            }

            // 6. Update Database
            $signature->update($dataToUpdate);
            // dd($dataToUpdate);

            return redirect()->route('admin.master_data.signature.index')->with('success', 'Signature updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update signature: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // 1. Ambil data Signature yang akan dihapus
        $signature = Signature::findOrFail($id);
        $fileName = $signature->filename;

        try {
            $signature->documentRoutingApprovals()->delete();
            // 2. Hapus file dari storage
            if (Storage::disk('local')->exists('private/signature/' . $fileName)) {
                Storage::disk('local')->delete('private/signature/' . $fileName);
            }

            // 3. Hapus record dari database
            $signature->delete();

            return redirect()->route('admin.master_data.signature.index')->with('success', 'Signature deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete signature: ' . $e->getMessage());
        }
    }

    public function getSignatureFile(String $id){
        
        $signature = Signature::findOrFail($id);
        $fileName = $signature->filename;
        $file = storage_path('app/private/signature/'.$fileName);

        return response()->file($file); 
    }
}
