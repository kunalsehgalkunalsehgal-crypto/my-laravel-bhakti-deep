<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#fff7e8;color:#3b2112;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fff7e8;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#fffdf8;border:1px solid #efd9b5;box-shadow:0 12px 36px rgba(117,63,18,.12);">
                    <tr>
                        <td style="height:7px;background:#e85b21;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:30px 24px 24px;background:#fff8ea;">
                            <div style="display:inline-block;width:54px;height:54px;line-height:54px;border:1px solid #d49a38;border-radius:50%;background:#fff1c9;color:#c8751d;font-family:Georgia,serif;font-size:25px;font-weight:bold;">BD</div>
                            <div style="margin-top:14px;color:#8b4b1d;font-family:Georgia,'Times New Roman',serif;font-size:25px;font-weight:bold;letter-spacing:1px;">BhaktiDeep</div>
                            <div style="margin-top:6px;color:#9b704d;font-size:11px;letter-spacing:2px;text-transform:uppercase;">Your virtual temple</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px 42px 38px;">
                            <div style="text-align:center;color:#d17a1f;font-size:22px;line-height:1;">&#10022; &nbsp; &#10022; &nbsp; &#10022;</div>
                            <h1 style="margin:22px 0 10px;text-align:center;color:#4a2817;font-family:Georgia,'Times New Roman',serif;font-size:27px;line-height:1.25;">Your sacred verification code</h1>
                            <p style="margin:0 auto;text-align:center;color:#765943;font-size:15px;line-height:1.7;">Namaste {{ $greetingName }},<br>Use this one-time code to {{ $context === 'register' ? 'complete your BhaktiDeep registration' : 'sign in to your BhaktiDeep account' }}.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:28px 0 22px;">
                                <tr>
                                    <td align="center" style="padding:22px 12px;background:#fff4d7;border:1px solid #e3b865;">
                                        <div style="color:#a35c18;font-size:11px;font-weight:bold;letter-spacing:3px;text-transform:uppercase;">One-time password</div>
                                        <div style="margin-top:10px;color:#b65f17;font-family:Georgia,'Times New Roman',serif;font-size:37px;font-weight:bold;letter-spacing:9px;">{{ $otp }}</div>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0;text-align:center;color:#8b6b51;font-size:13px;line-height:1.6;">This code expires in <strong style="color:#a35c18;">10 minutes</strong>.<br>For your security, please do not share it with anyone.</p>
                            <div style="height:1px;margin:30px 0 22px;background:#edd9ba;font-size:0;">&nbsp;</div>
                            <p style="margin:0;text-align:center;color:#8b6b51;font-family:Georgia,'Times New Roman',serif;font-size:15px;font-style:italic;">Har Deep Mein Bhakti</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 24px;background:#4a2817;color:#f7dfb2;font-size:11px;line-height:1.7;">
                            This is an automated message from BhaktiDeep.<br>
                            Please do not reply to this email.
                        </td>
                    </tr>
                </table>
                <div style="padding:14px 10px 0;color:#9a795e;font-size:11px;">A peaceful digital space for your prayers and rituals.</div>
            </td>
        </tr>
    </table>
</body>
</html>