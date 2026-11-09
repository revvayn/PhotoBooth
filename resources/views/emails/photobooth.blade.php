@php($brand = \App\Support\Brand::get())
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Foto {{ $brand['name'] }}</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.5; padding: 20px; background: #f8fafc;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="padding: 20px 24px; color: #ffffff; background: linear-gradient(90deg, {{ $brand['primary'] }}, {{ $brand['secondary'] }});">
            <h2 style="margin: 0; font-size: 20px;">{{ $brand['name'] }}</h2>
            <p style="margin: 4px 0 0; opacity: .85; font-size: 13px;">{{ $brand['tagline'] }}</p>
        </div>
        <div style="padding: 24px;">
            <h2 style="margin-top: 0;">Halo!</h2>
            <p>Terima kasih sudah foto di <strong>{{ $brand['name'] }}</strong>!</p>
            <p>Nomor Antrian Anda: <strong style="color: {{ $brand['primary'] }};">{{ $queueNumber }}</strong></p>
            @if (count($strips) > 0)
                <p>Hasil foto Anda terlampir pada email ini ({{ count($strips) }} file).</p>
            @else
                <p>Terima kasih telah berkunjung. Sampai jumpa lagi!</p>
            @endif
            <br>
            <p>Salam hangat,<br><strong>Tim {{ $brand['name'] }}</strong></p>
        </div>
        <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
            &copy; {{ date('Y') }} {{ $brand['footer'] }}
        </div>
    </div>
</body>
</html>
