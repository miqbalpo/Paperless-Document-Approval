<?php

namespace App\Jobs\Approval;

use App\Models\DocumentRoutingApproval;
use App\Services\Approval\RequestApprovalMailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendRequestApprovalMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public DocumentRoutingApproval $documentRoutingApproval;

    public function __construct(DocumentRoutingApproval $documentRoutingApproval)
    {
        $this->documentRoutingApproval = $documentRoutingApproval;
    }

    /**
     * Execute the job.
     */
    public function handle(RequestApprovalMailerService $mailerService): void
    {
        try {
            DB::beginTransaction();

            $mailerService->sendRequestApprovalMail($this->documentRoutingApproval);
            $this->documentRoutingApproval->email_sent_at = now();
            $this->documentRoutingApproval->save();
            
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error("Error send email : ". $e);
        }
    }
}
