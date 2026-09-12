<?php
$base = 'http://127.0.0.1:8000';
$cookie = 'D:/PROJEK/PhotoBooth/storage/app/flow3-cookie.txt';
$photosDir = 'D:/PROJEK/PhotoBooth/storage/app/public/photos/2sTPDn8xWxitQNXG7QDjjHyUOloB990CzbnJF22c';
@unlink($cookie);

function flowGet($url, $cookie) {
    $h = curl_init($url);
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b, ''];
}

function flowForm($url, $cookie, $fields) {
    $h = curl_init($url);
    curl_setopt($h, CURLOPT_POST, true);
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($h, CURLOPT_POSTFIELDS, $fields);
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b];
}

function flowJson($url, $cookie, $fields) {
    $h = curl_init($url);
    curl_setopt($h, CURLOPT_POST, true);
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($h, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json', 'X-Requested-With: XMLHttpRequest']);
    curl_setopt($h, CURLOPT_POSTFIELDS, json_encode($fields));
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b];
}
function tok($html) { preg_match('/name="_token" value="([^"]+)"/', $html, $m); return $m[1] ?? ''; }

// 1. mulai
list($c, $b, $e) = flowGet($base.'/mulai', $cookie);
$t = tok($b);
echo "mulai: $c tok=" . ($t ? 'ok' : 'MISSING') . "\n";
if (! $t) { file_put_contents('D:/PROJEK/PhotoBooth/storage/app/mulai-probe.html', $b); exit; }

// 2. pilih frame fr
list($c, $b) = flowForm($base.'/frame', $cookie, ['_token' => $t, 'frame' => 'fr']);
echo "frame: $c\n";

// 3. upload 4 foto (base64)
$photos = [];
for ($i = 1; $i <= 4; $i++) {
    $photos[] = base64_encode(file_get_contents("$photosDir/foto-$i.jpg"));
}
list($c, $b) = flowJson($base.'/foto', $cookie, ['_token' => $t, 'photos' => $photos]);
echo "foto: $c :: " . substr($b, 0, 120) . "\n";

// 4. filter, print(JSON?), metode
list($c, $b) = flowJson($base.'/filter', $cookie, ['_token' => $t, 'filter' => 'asli']);
echo "filter: $c\n";
list($c, $b) = flowForm($base.'/print', $cookie, ['_token' => $t, 'copy' => 2]);
echo "print: $c\n";
list($c, $b) = flowForm($base.'/metode', $cookie, ['_token' => $t, 'metode' => 'cash']);
echo "metode: $c\n";
list($c, $b) = flowJson($base.'/pembayaran', $cookie, ['_token' => $t]);
echo "pembayaran: $c\n";

// 5. review
list($c, $b) = flowGet($base.'/review', $cookie);
echo "review: $c len=" . strlen($b) . "\n";
preg_match("/data-photos='(.*?)'/s", $b, $m);
echo "photos: " . (isset($m[1]) ? substr($m[1], 0, 200) : 'MISSING') . "\n";
preg_match('/data-frame-image="([^"]*)"/', $b, $m);
echo "frameImage: " . ($m[1] ?? 'MISSING') . "\n";
preg_match("/data-frame-slots='(.*?)'/s", $b, $m);
echo "slots: " . (isset($m[1]) ? substr($m[1], 0, 200) : 'MISSING') . "\n";
file_put_contents('D:/PROJEK/PhotoBooth/storage/app/review-probe.html', $b);
echo "probe saved\n";