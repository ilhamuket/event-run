<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Diterima</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    {{-- Wrapper --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 16px;">

                {{-- Card --}}
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #991b1b, #7f1d1d); padding: 40px 32px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700;">
                                Pendaftaran Diterima! 🎉
                            </h1>
                            <p style="margin: 8px 0 0; color: #fecaca; font-size: 14px;">
                                {{ $participant->event->name }}
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 32px;">

                            <p style="margin: 0 0 20px; color: #374151; font-size: 15px; line-height: 1.6;">
                                Halo <strong>{{ $participant->name }}</strong>,
                            </p>

                            <p style="margin: 0 0 24px; color: #374151; font-size: 15px; line-height: 1.6;">
                                Terima kasih telah mendaftar di <strong>{{ $participant->event->name }}</strong>.
                                Pendaftaran Anda telah kami terima dan sedang menunggu pembayaran.
                            </p>

                            {{-- Status Badge --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center">
                                        <span style="display: inline-block; padding: 8px 20px; background-color: #fef3c7; color: #92400e; font-size: 13px; font-weight: 600; border-radius: 20px;">
                                            ⏳ MENUNGGU PEMBAYARAN
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            {{-- Detail Box --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #fef2f2; border-radius: 12px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px; color: #991b1b; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Detail Pendaftaran
                                        </h3>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px; width: 140px;">Nama</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">{{ $participant->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">BIB Name</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">{{ $participant->bib_name ?? $participant->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Kategori</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">{{ $participant->category?->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Jersey Size</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">{{ $participant->jersey_size }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Gender</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">{{ $participant->gender === 'M' ? 'Pria' : 'Wanita' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Payment Detail Box --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px; color: #374151; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Detail Pembayaran
                                        </h3>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px; width: 140px;">No. Referensi</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">{{ $transaction->merchant_ref }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Metode Bayar</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">{{ $transaction->payment_name ?? 'QRIS' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Biaya Registrasi</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Biaya Admin</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 13px; font-weight: 600;">Rp {{ number_format($transaction->fee, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding: 12px 0 0; border-top: 1px solid #e5e7eb;"></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #111827; font-size: 15px; font-weight: 700;">Total Bayar</td>
                                                <td style="padding: 6px 0; color: #991b1b; font-size: 15px; font-weight: 700;">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                            </tr>
                                            @if($transaction->expired_at)
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 13px;">Batas Bayar</td>
                                                <td style="padding: 6px 0; color: #dc2626; font-size: 13px; font-weight: 600;">{{ \Carbon\Carbon::parse($transaction->expired_at)->format('d M Y, H:i') }} WIB</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- CTA Button --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('event.payment.show', ['event' => $participant->event->slug, 'ref' => $transaction->merchant_ref]) }}"
                                           style="display: inline-block; padding: 14px 40px; background-color: #991b1b; color: #ffffff; font-size: 15px; font-weight: 700; text-decoration: none; border-radius: 10px;">
                                            Bayar Sekarang
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Warning --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.5;">
                                            <strong>⚠️ Penting:</strong> Segera lakukan pembayaran sebelum batas waktu yang ditentukan.
                                            Jika melewati batas waktu, pendaftaran Anda akan otomatis dibatalkan.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; color: #6b7280; font-size: 13px; line-height: 1.6;">
                                Jika Anda memiliki pertanyaan, silakan hubungi panitia melalui kontak yang tersedia di halaman event.
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center;">
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                Email ini dikirim otomatis oleh sistem {{ config('app.name') }}.
                            </p>
                            <p style="margin: 4px 0 0; color: #9ca3af; font-size: 12px;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
