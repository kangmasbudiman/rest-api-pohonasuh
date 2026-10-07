<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hadiah Adopsi Pohon</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f5;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f5;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(6,35,26,0.08);">
                    {{-- Header hijau emerald --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#059669,#0d9488);background-color:#059669;padding:32px 32px 28px;text-align:center;">
                            <div style="font-size:40px;line-height:1;">🌳</div>
                            <h1 style="margin:12px 0 0;color:#ffffff;font-size:22px;font-weight:bold;">Pohon Asuh</h1>
                            <p style="margin:4px 0 0;color:#d1fae5;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Adopt · Care · Grow</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;color:#064e3b;">Hai <strong>{{ $toName }}</strong>,</p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#374151;">
                                <strong>{{ $fromName }}</strong> menghadiahkan adopsi pohon untuk Anda melalui program Pohon Asuh.
                            </p>
                            @if (!empty($pohon))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;border:1px solid #d1fae5;border-radius:12px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:14px;color:#065f46;background-color:#ecfdf5;">
                                        🌲 {{ $pohon }}
                                    </td>
                                </tr>
                            </table>
                            @endif
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#374151;">
                                Buka tautan di bawah untuk melihat, mencetak, atau menyimpan sertifikat adopsi atas nama Anda.
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $link }}" style="display:inline-block;background-color:#059669;color:#ffffff;text-decoration:none;font-size:15px;font-weight:bold;padding:14px 36px;border-radius:9999px;">Lihat Sertifikat Saya</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:20px 0 0;font-size:12px;color:#6b7280;word-break:break-all;">
                                Tautan: {{ $link }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background-color:#f0fdf4;border-top:1px solid #d1fae5;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#047857;">
                                Email ini dikirim karena Anda menerima hadiah adopsi pohon dari {{ $fromName }}.
                            </p>
                            <p style="margin:6px 0 0;font-size:12px;color:#6b7280;">
                                &copy; Pohon Asuh — yayasan konservasi bersama masyarakat
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
