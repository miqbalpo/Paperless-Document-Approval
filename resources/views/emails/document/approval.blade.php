<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Approval Request - CNN Indonesia</title>
</head>

<body style="
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    padding: 40px 20px;
    margin: 0;
">
    <div style="
        max-width: 600px;
        margin: auto;
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    ">
        <!-- Header dengan gradient -->
        <div style="
            background: linear-gradient(135deg, #ef5350 0%, #e53935 100%);
            padding: 40px 40px 50px;
            position: relative;
        ">
            <div>
                <img src="{{$logoCid ?? '#'}}" alt="CNN Indonesia" style="width: 60px; height: auto;">
            </div>
            <h2 style="
                font-size: 28px;
                margin: 0;
                font-weight: 700;
                color: #ffffff;
                letter-spacing: -0.5px;
            ">
                {{$document->title ?? 'Default Document Title'}}
            </h2>
        </div>
        <div style="padding: 40px;">
            <p style="
                font-size: 16px;
                color: #1a1a1a;
                margin: 0 0 10px;
                font-weight: 600;
            ">
                Hi, {{$user->name ?? "Default User Name"}}! 👋
            </p>
            
            <p style="
                font-size: 15px;
                color: #555;
                line-height: 1.8;
                margin: 0 0 30px;
            ">
                Dokumen ini memerlukan persetujuan Anda agar dapat diproses lebih lanjut. 
                Silakan tinjau kembali detailnya melalui tautan di bawah ini agar proses dapat berjalan dengan lancar. 
                Terima kasih atas perhatian dan kerja samanya.
            </p>
            <div style="margin: 35px 0;">
                <a href="{{$requestApprovalLink ?? "#"}}" style="
                    display: inline-block;
                    background: linear-gradient(135deg, #ef5350 0%, #e53935 100%);
                    color: #ffffff;
                    padding: 14px 32px;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    text-decoration: none;
                    font-size: 15px;
                    box-shadow: 0 4px 15px rgba(239, 83, 80, 0.3);
                    transition: all 0.3s ease;
                ">
                    📄 Menuju Dokumen
                </a>
            </div>

            <!-- Info box -->
            <div style="
                background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
                border-radius: 12px;
                padding: 20px;
                margin-top: 30px;
                border-left: 4px solid #ef5350;
            ">
                <p style="
                    font-size: 15px;
                    color: #1a1a1a;
                    margin: 0 0 8px;
                    font-weight: 700;
                ">
                    🏢 CNN Indonesia
                </p>
                <p style="
                    font-size: 14px;
                    color: #555;
                    margin: 0;
                    line-height: 1.6;
                ">
                    📧 Contact Us: <a href="mailto:event.information@cnn.id" style="color: #e53935; text-decoration: none; font-weight: 600;">event.information@cnn.id</a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        ">
            <p style="
                font-size: 12px;
                color: #999;
                margin: 0;
            ">
                © 2025 CNN Indonesia. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>