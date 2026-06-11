<?php

namespace App\Mail\Approval;

use App\Models\DocumentRoutingApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class RequestApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentRoutingApproval $documentRoutingApproval;

    public function __construct(DocumentRoutingApproval $documentRoutingApproval)
    {
        $this->documentRoutingApproval = $documentRoutingApproval;
    }

    public function build()
    {
        $requestApprovalLink = route('document.approval.index', [
            'email' => $this->documentRoutingApproval->user->email,
            'key'   => $this->documentRoutingApproval['key'],
        ]);

        // Generate unique CID for the logo
        $logoCid = 'cnn-logo-' . uniqid() . '@paperless.id';
        $logoPath = public_path('CNNIDN.png');

        if (!file_exists($logoPath)) {
            return $this->view('emails.document.approval')
                ->with([
                    'requestApprovalLink' => $requestApprovalLink,
                    'user'                => $this->documentRoutingApproval->user,
                    'document'            => $this->documentRoutingApproval->document,
                    'logoCid'             => null
                ])
                ->subject('Document Approval Request - CNN Indonesia');
        }

        return $this->view('emails.document.approval')
            ->with([
                'requestApprovalLink' => $requestApprovalLink,
                'user'                => $this->documentRoutingApproval->user,
                'document'            => $this->documentRoutingApproval->document,
                'logoCid'             => "cid:$logoCid"
            ])
            ->withSymfonyMessage(function (Email $message) use ($logoPath, $logoCid) {
                $image = new DataPart(fopen($logoPath, 'r'), 'CNNIDN.png', 'image/png');
                $image->asInline();
                $image->setContentId($logoCid);
                $message->addPart($image);
            })
            ->subject('Document Approval Request - CNN Indonesia');
    }
}
