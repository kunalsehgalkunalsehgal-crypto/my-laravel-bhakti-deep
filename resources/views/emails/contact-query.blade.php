<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BhaktiDeep Contact Form Query</title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#fff7e8;
    font-family:Arial,Helvetica,sans-serif;
    color:#3b2112;
">

<table width="100%" cellpadding="0" cellspacing="0"
       role="presentation"
       style="background:#fff7e8;">

    <tr>
        <td align="center" style="padding:28px 12px;">

            <table width="100%" cellpadding="0" cellspacing="0"
                   role="presentation"
                   style="
                       max-width:620px;
                       background:#fffdf8;
                       border:1px solid #efd9b5;
                   ">

                <!-- Top Border -->
                <tr>
                    <td style="
                        height:7px;
                        background:#e85b21;
                    "></td>
                </tr>

                <!-- Branding -->
                <tr>
                    <td align="center" style="
                        padding:30px 20px;
                        background:#fff8ea;
                    ">

                        <div style="
                            display:inline-block;
                            width:54px;
                            height:54px;
                            line-height:54px;
                            border-radius:50%;
                            background:#fff1c9;
                            border:1px solid #d49a38;
                            color:#c8751d;
                            font-size:25px;
                            font-weight:bold;
                            font-family:Georgia,serif;
                        ">
                            BD
                        </div>

                        <h2 style="
                            color:#8b4b1d;
                            font-family:Georgia,serif;
                            font-size:25px;
                            margin:15px 0 5px;
                        ">
                            BhaktiDeep
                        </h2>

                        <p style="
                            color:#9b704d;
                            font-size:11px;
                            letter-spacing:2px;
                            margin:0;
                        ">
                            YOUR VIRTUAL TEMPLE
                        </p>

                    </td>
                </tr>

                <!-- Email Content -->
                <tr>
                    <td style="padding:30px 25px;">

                        <h1 style="
                            text-align:center;
                            color:#4a2817;
                            font-family:Georgia,serif;
                            font-size:25px;
                            margin:0 0 12px;
                        ">
                            New Contact Form Query
                        </h1>

                        <p style="
                            text-align:center;
                            color:#765943;
                            font-size:14px;
                            line-height:1.6;
                        ">
                            A new enquiry has been submitted
                            through the BhaktiDeep website.
                        </p>

                        <!-- Details -->
                        <table width="100%"
                               cellpadding="12"
                               cellspacing="0"
                               role="presentation"
                               style="
                                   margin-top:25px;
                                   background:#fff4d7;
                                   border:1px solid #e3b865;
                                   font-size:14px;
                               ">

                            <tr>
                                <td style="border-bottom:1px solid #ecd3a7;">
                                    <strong>Name</strong>
                                </td>

                                <td style="border-bottom:1px solid #ecd3a7;">
                                    {{ $details['full_name'] }}
                                </td>
                            </tr>

                            <tr>
                                <td style="border-bottom:1px solid #ecd3a7;">
                                    <strong>Mobile</strong>
                                </td>

                                <td style="border-bottom:1px solid #ecd3a7;">
                                    {{ $details['mobile'] }}
                                </td>
                            </tr>

                            <tr>
                                <td style="border-bottom:1px solid #ecd3a7;">
                                    <strong>Email</strong>
                                </td>

                                <td style="border-bottom:1px solid #ecd3a7;">
                                    {{ $details['email'] ?? 'Not provided' }}
                                </td>
                            </tr>

                            <tr>
                                <td style="border-bottom:1px solid #ecd3a7;">
                                    <strong>Query Type</strong>
                                </td>

                                <td style="border-bottom:1px solid #ecd3a7;">
                                    {{ $details['query_type'] }}
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Preferred Service</strong>
                                </td>

                                <td>
                                    {{ $details['preferred_service'] ?? 'Not selected' }}
                                </td>
                            </tr>

                        </table>

                        <!-- Message -->
                        <h3 style="
                            margin:28px 0 12px;
                            color:#4a2817;
                            font-size:18px;
                        ">
                            Customer Message
                        </h3>

                        <div style="
                            padding:20px;
                            background:#ffffff;
                            border:1px solid #efd9b5;
                            color:#765943;
                            font-size:14px;
                            line-height:1.7;
                            overflow-wrap:break-word;
                        ">
                            {!! nl2br(e($details['message'])) !!}
                        </div>

                        <p style="
                            text-align:center;
                            color:#8b6b51;
                            font-size:13px;
                            margin-top:28px;
                        ">
                            Please review this enquiry
                            and respond to the customer.
                        </p>

                        <p style="
                            text-align:center;
                            color:#8b6b51;
                            font-family:Georgia,serif;
                            font-style:italic;
                            margin-top:25px;
                        ">
                            Har Deep Mein Bhakti
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="
                        padding:20px;
                        background:#4a2817;
                        color:#f7dfb2;
                        font-size:12px;
                        line-height:1.6;
                    ">
                        This enquiry was submitted through
                        the BhaktiDeep Contact Form.
                    </td>
                </tr>

            </table>

        </td>
    </tr>

</table>

</body>
</html>