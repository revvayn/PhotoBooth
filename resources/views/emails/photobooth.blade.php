<!DOCTYPE html>
<html>
<head>
    <title>Hasil Foto Photobooth</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.5; padding: 20px;">
    <h2>Halo!</h2>
    <p>Terima kasih sudah foto di <strong>Photobooth</strong>!</p>
    <p>Nomor Antrian Anda: <strong>{{ $queueNumber }}</strong></p>
    @if (count($strips) > 0)
        <p>Hasil foto Anda terlampir pada email ini ({{ count($strips) }} file).</p>
    @else
        <p>Terima kasih telah berkunjung. Sampai jumpa lagi!</p>
    @endif
    <br>
    <p>Salam hangat,<br><strong>Tim Photobooth</strong></p>
</body>
</html>
