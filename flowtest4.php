<?php
$base = 'http://127.0.0.1:8000';
$cookie = 'D:/PROJEK/PhotoBooth/storage/app/flow4-cookie.txt';
$photosDir = 'D:/PROJEK/PhotoBooth/storage/app/public/photos/2sTPDn8xWxitQNXG7QDjjHyUOloB990CzbnJF22c';
@unlink($cookie);

function fget($url, $cookie) {
    $h = curl_init($url);
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_COOKIEJAR, $cookie); curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b];
}
function fpost($url, $cookie, $fields, $token) {
    $fields['_token'] = $token;
    $h = curl_init($url);
    curl_setopt($h, CURLOPT_POST, true);
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_COOKIEJAR, $cookie); curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($h, CURLOPT_POSTFIELDS, http_build_query($fields));
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b];
}
function fjson($url, $cookie, $fields, $token) {
    $fields['_token'] = $token;
    $h = curl_init($url);
    curl_setopt($h, CURLOPT_POST, true); curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_COOKIEJAR, $cookie); curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($h, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json', 'X-Requested-With: XMLHttpRequest']);
    curl_setopt($h, CURLOPT_POSTFIELDS, json_encode($fields));
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b];
}
function tok($html) { preg_match('/name="_token" value="([^"]+)"/', $html, $m); return $m[1] ?? ''; }

// 1. welcome -> token
list($c, $b) = fget($base.'/', $cookie);
$t = tok($b);
echo "welcome: $c tok=" . ($t ? 'ok' : 'MISSING') . "\n";
if (! $t) { file_put_contents('D:/PROJEK/PhotoBooth/storage/app/welcome-probe.html', $b); exit; }

// 2. mulai
list($c, $b) = fpost($base.'/mulai', $cookie, [], $t);
$t = tok($b);
echo "mulai: $c\n";

// 3. pilih frame fr
list($c, $b) = fpost($base.'/frame', $cookie, ['frame' => 'fr'], $t);
echo "frame: $c\n";

// 4. foto upload JSON
$photos = [];
for ($i = 1; $i <= 4; $i++) { $photos[] = base64_encode(file_get_contents("$photosDir/foto-$i.jpg")); }
list($c, $b) = fjson($base.'/foto', $cookie, ['photos' => $photos], $t);
echo "foto: $c :: " . substr($b, 0, 120) . "\n";

// 5. filter asli, print copy=2, metode cash
list($c, $b) = fjson($base.'/filter', $cookie, ['filter' => 'asli'], $t);
echo "filter: $c\n";
list($c, $b) = fjson($base.'/print', $cookie, ['copy' => 2], $t);
echo "print: $c\n";
list($c, $b) = fjson($base.'/metode', $cookie, ['metode' => 'cash'], $t);
echo "metode: $c\n";
list($c, $b) = fjson($base.'/pembayaran', $cookie, [], $t);
echo "pembayaran: $c\n";

// 6. review
list($c, $b) = fget($base.'/review', $cookie);
echo "review: $c len=" . strlen($b) . "\n";
preg_match("/data-photos='(.*?)'/s", $b, $m);
echo "photos: " . (isset($m[1]) ? substr($m[1], 0, 200) : 'MISSING') . "\n";
preg_match('/data-frame-image="([^"]*)"/', $b, $m);
echo "frameImage: " . ($m[1] ?? 'MISSING') . "\n";
preg_match("/data-frame-slots='(.*?)'/s", $b, $m);
echo "slots: " . (isset($m[1]) ? substr($m[1], 0, 200) : 'MISSING') . "\n";
file_put_contents('D:/PROJEK/PhotoBooth/storage/app/review-real.html', $b);