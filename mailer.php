<?php
function sendMail($to, $subject, $message, $fromName = "Ariel’s Barbershop")
{
    $host = 'smtp.gmail.com';
    $port = 587;
    $username = 'jhondarren86@gmail.com';
    $password = 'ppza fhgw tyzy mblr'; // Gmail App Password
    $from = $username;

    $fp = fsockopen($host, $port, $errno, $errstr, 30);
    if (!$fp) {
        die("Connection failed: $errstr ($errno)");
    }

    // Function to send command
    $sendCmd = function($fp, $cmd) {
        fwrite($fp, $cmd . "\r\n");
        $response = '';
        while ($line = fgets($fp, 515)) {
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) break;
        }
        return $response;
    };

    fgets($fp); // Server greeting
    $sendCmd($fp, "EHLO localhost");
    $sendCmd($fp, "STARTTLS");

    if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($fp);
        die("TLS negotiation failed");
    }

    $sendCmd($fp, "EHLO localhost");
    $sendCmd($fp, "AUTH LOGIN");
    $sendCmd($fp, base64_encode($username));
    $sendCmd($fp, base64_encode($password));

    $sendCmd($fp, "MAIL FROM:<$from>");
    $sendCmd($fp, "RCPT TO:<$to>");
    $sendCmd($fp, "DATA");

    // ===========================
    // PROFESSIONAL HTML EMAIL
    // ===========================

    $htmlBody = '
    <div style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6f9;padding:30px;">
        <div style="max-width:600px;margin:auto;background:#ffffff;border-radius:15px;
                    box-shadow:0 10px 25px rgba(0,0,0,0.1);overflow:hidden;">
            
            <div style="background:linear-gradient(90deg,#0b3d91,#c1121f);
                        padding:20px;text-align:center;color:#ffffff;">
                <h1 style="margin:0;font-size:22px;">
                    ✂ Ariel’s Barbershop
                </h1>
                <p style="margin-top:5px;font-size:13px;opacity:0.9;">
                    Appointment Notification
                </p>
            </div>

            <div style="padding:25px;color:#333;font-size:15px;line-height:1.6;">
                '.$message.'
            </div>

            <div style="background:#f3f4f6;text-align:center;padding:12px;
                        font-size:12px;color:#6b7280;">
                © '.date("Y").' Ariel’s Barbershop. All rights reserved.
            </div>

        </div>
    </div>
    ';

    $headers  = "From: $fromName <$from>\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    fwrite($fp, $headers . "\r\n" . $htmlBody . "\r\n.\r\n");

    $sendCmd($fp, "QUIT");
    fclose($fp);

    return true;
}
?>
