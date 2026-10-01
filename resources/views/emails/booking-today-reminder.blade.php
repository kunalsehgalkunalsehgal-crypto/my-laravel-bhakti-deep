<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $details['subject'] ?? 'Today booking reminder | BhaktiDeep' }}</title>
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

                        <h1 style="margin:22px 0 10px;text-align:center;color:#4a2817;font-family:Georgia,'Times New Roman',serif;font-size:27px;line-height:1.25;">
                            {{ $details['heading'] ?? 'Aaj aapki booking hai' }}
                        </h1>

                        <p style="margin:0 auto;text-align:center;color:#765943;font-size:15px;line-height:1.7;">
                            Namaste {{ $details['recipient_name'] ?? 'Devotee' }},<br>
                            Aaj aapki <strong style="color:#a35c18;">{{ $details['service_name'] ?? 'BhaktiDeep Seva' }}</strong> scheduled hai.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:28px 0 22px;background:#fff4d7;border:1px solid #e3b865;">
                            <tr>
                                <td style="padding:11px 16px;color:#8b6b51;font-size:13px;">Seva</td>
                                <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;">{{ $details['service_name'] ?? '-' }}</td>
                            </tr>

                            @if(!empty($details['package_name']))
                                <tr>
                                    <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Package</td>
                                    <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['package_name'] }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Date</td>
                                <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['booking_date'] ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Time</td>
                                <td align="right" style="padding:11px 16px;color:#b65f17;font-size:16px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['slot'] ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Mode</td>
                                <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['mode_label'] ?? '-' }}</td>
                            </tr>

                            @if(($details['recipient_role'] ?? '') === 'user')
                                <tr>
                                    <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Pandit</td>
                                    <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['pandit_name'] ?? '-' }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Devotee</td>
                                    <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['devotee_name'] ?? '-' }}</td>
                                </tr>
                            @endif

                            @if(($details['mode_label'] ?? '') === 'Offline' && !empty($details['location']))
                                <tr>
                                    <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Location</td>
                                    <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['location'] }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td style="padding:11px 16px;color:#8b6b51;font-size:13px;border-top:1px solid #ecd3a7;">Booking Ref.</td>
                                <td align="right" style="padding:11px 16px;color:#4a2817;font-size:13px;font-weight:bold;border-top:1px solid #ecd3a7;">{{ $details['booking_reference'] ?? '-' }}</td>
                            </tr>
                        </table>

                        <p style="margin:0;text-align:center;color:#765943;font-size:14px;line-height:1.75;">
                            {{ $details['instruction_text'] ?? 'Please apne booking time se pehle ready rahein.' }}
                        </p>

                        @if(!empty($details['action_url']))
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:26px auto 0;">
                                <tr>
                                    <td align="center" bgcolor="#e85b21" style="border-radius:5px;">
                                        <a href="{{ $details['action_url'] }}" style="display:inline-block;padding:13px 24px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;">
                                            {{ $details['action_label'] ?? 'Open BhaktiDeep' }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <div style="height:1px;margin:30px 0 22px;background:#edd9ba;font-size:0;">&nbsp;</div>
                        <p style="margin:0;text-align:center;color:#8b6b51;font-family:Georgia,'Times New Roman',serif;font-size:15px;font-style:italic;">Har Deep Mein Bhakti</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:18px 24px;background:#4a2817;color:#f7dfb2;font-size:11px;line-height:1.7;">
                        This is an automated booking reminder from BhaktiDeep.<br>
                        Please do not reply to this email.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
