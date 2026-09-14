<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo messaggio dal sito</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f1ec;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f1ec;padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border:1px solid #e7e1d8;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#191817;padding:28px 32px;">
                            <h1 style="margin:0;color:#fff2e6;font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:300;letter-spacing:1px;">
                                Nuovo messaggio dal sito
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 24px;color:#555;font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;">
                                Hai ricevuto una nuova richiesta tramite il modulo di contatto.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border-top:1px solid #e7e1d8;">
                                <tr>
                                    <td style="padding:14px 0;border-bottom:1px solid #e7e1d8;">
                                        <p style="margin:0;color:#999;font-family:Helvetica,Arial,sans-serif;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Nome</p>
                                        <p style="margin:6px 0 0;color:#191817;font-family:Helvetica,Arial,sans-serif;font-size:16px;">{{ $name }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 0;border-bottom:1px solid #e7e1d8;">
                                        <p style="margin:0;color:#999;font-family:Helvetica,Arial,sans-serif;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Email</p>
                                        <p style="margin:6px 0 0;color:#191817;font-family:Helvetica,Arial,sans-serif;font-size:16px;">
                                            <a href="mailto:{{ $email }}" style="color:#191817;">{{ $email }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 12px;color:#999;font-family:Helvetica,Arial,sans-serif;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Messaggio</p>
                            <div style="background-color:#f9f6f1;border-left:4px solid #191817;padding:20px 24px;margin:0 0 32px;">
                                <p style="margin:0;color:#444;font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;white-space:pre-line;">{{ $messageBody }}</p>
                            </div>

                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background-color:#191817;border-radius:4px;">
                                        <a href="mailto:{{ $email }}" style="display:inline-block;padding:13px 28px;color:#fff2e6;font-family:Helvetica,Arial,sans-serif;font-size:13px;letter-spacing:2px;text-transform:uppercase;text-decoration:none;">
                                            Rispondi
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f9f6f1;padding:18px 32px;border-top:1px solid #e7e1d8;">
                            <p style="margin:0;color:#999;font-family:Helvetica,Arial,sans-serif;font-size:12px;">
                                Inviato automaticamente dal sito.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>