<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return redirect()->route('dashboard.inbox.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/master_data')
    ->group(function () {
        Route::resource('departement', \App\Http\Controllers\Admin\MasterData\DepartementController::class)
            ->names('admin.master_data.departement');
        Route::resource('position', \App\Http\Controllers\Admin\MasterData\PositionController::class)
            ->names('admin.master_data.position');
        Route::resource('document_type', \App\Http\Controllers\Admin\MasterData\DocumentTypeController::class)
            ->names('admin.master_data.document_type');
        Route::resource('employee', \App\Http\Controllers\Admin\MasterData\EmployeeController::class)
            ->names('admin.master_data.employee');
        Route::resource('signature', \App\Http\Controllers\Admin\MasterData\SignatureController::class)
            ->names('admin.master_data.signature');
        Route::get("/signature/get_signature_file/{id}", [\App\Http\Controllers\Admin\MasterData\SignatureController::class, 'getSignatureFile'])->name('admin.master_data.signature.get_signature_file');
        Route::resource('user', \App\Http\Controllers\Admin\MasterData\UserController::class)
            ->names('admin.master_data.user');
        });
        
Route::middleware(['auth', 'role:admin|approver'])
        ->group(function () {
            
            Route::get('dashboard/inbox', [\App\Http\Controllers\Dashboard\InboxController::class, 'index'])
                ->name('dashboard.inbox.index');
            Route::get('dashboard/report', [\App\Http\Controllers\Dashboard\ReportController::class, 'index'])
                ->name('dashboard.report.index');
            Route::get('dashboard/report/export', [\App\Http\Controllers\Dashboard\ReportController::class, 'export'])
                ->name('dashboard.report.export');
          
        Route::resource('document', \App\Http\Controllers\Document\DocumentController::class)
            ->names('document');

        Route::get('document/{document}/preview', [\App\Http\Controllers\Document\DocumentController::class, 'getPdfFile'])
            ->name('document.preview');

        Route::get('document/{documentRoutingApproval}/positioning', [\App\Http\Controllers\Document\PositioningController::class, 'index'])
            ->name('document.positioning.index');

        Route::post('document/{documentRoutingApproval}/positioning', [\App\Http\Controllers\Document\PositioningController::class, 'store'])
            ->name('document.positioning.store');

        Route::delete('document/{documentRoutingApproval}/positioning', [\App\Http\Controllers\Document\PositioningController::class, 'destroy'])
            ->name('document.positioning.destroy');
        
        Route::get('document/{document}/getSignedPdfFile', [\App\Http\Controllers\Document\DocumentController::class, 'getSignedPdfFile'])
            ->name('document.get_signed_document');
            
    });

Route::prefix('document/approval')->group(
    function () {
        Route::middleware(['auth', 'role:admin|approver'])->group(function () {
            Route::post('{document}/send_approval_email', [\App\Http\Controllers\Document\ApprovalController::class, 'sendApprovalEmail'])
                ->name('document.approval.send_approval_email');
        });
        Route::get('{email}/{key}', [\App\Http\Controllers\Document\ApprovalController::class, 'index'])->name('document.approval.index');
        Route::post('{email}/{key}/approve', [\App\Http\Controllers\Document\ApprovalController::class, 'approve'])->name('document.approval.approve');
        Route::post('{email}/{key}/reject', [\App\Http\Controllers\Document\ApprovalController::class, 'reject'])->name('document.approval.reject');
        Route::post('{email}/{key}/upload-signature', [\App\Http\Controllers\Document\ApprovalController::class, 'uploadSignature'])->name('document.approval.upload_signature');
        Route::get('{email}/{key}/get_signature_file', [\App\Http\Controllers\Document\ApprovalController::class, 'getSignatureFile'])->name('document.approval.get_signature_file');
        Route::get('{email}/{key}/get_pdf_file', [\App\Http\Controllers\Document\ApprovalController::class, 'getPdfFile'])->name('document.approval.get_pdf_file');
    }
);


// ONLY FOR DEV
Route::prefix('only-for-dev')->group(function () {
    Route::get('email-body', function () {
        return view('emails.document.approval');
    })->name('email.document.approval');
});
// END OF - ONLY FOR DEV



require __DIR__ . '/auth.php';
