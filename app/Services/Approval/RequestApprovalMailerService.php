<?php
namespace App\Services\Approval;

use App\Mail\Approval\RequestApprovalMail;
use App\Models\DocumentRoutingApproval;
use Illuminate\Support\Facades\Mail;

class RequestApprovalMailerService
{
    public function sendRequestApprovalMail(DocumentRoutingApproval $documentRoutingApproval){
        Mail::to($documentRoutingApproval->user->email)->send(new RequestApprovalMail($documentRoutingApproval));
    }
}
