<?php
$base = 'http://127.0.0.1:8000';
$cookie = 'D:/PROJEK/PhotoBooth/storage/app/flow-cookie.txt';
$photosDir = 'D:/PROJEK/PhotoBooth/storage/app/public/photos/2sTPDn8xWxitQNXG7QDjjHyUOloB990CzbnJF22c';
@unlink($cookie);

function gurl($method, $url, $cookie, $fields = null, $json = false, $token = '') {
    $h = curl_init($url);
    if ($json) { curl_setopt($h, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json', 'X-Requested-With: XMLHttpRequest']); }
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_COOKIEJAR, $cookie); curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, $method === 'GET');
    if ($method === 'POST') { curl_setopt($h, CURLOPT_POST, true); }
    if ($fields !== null) {
        if ($json) { curl_setopt($h, CURLOPT_POSTFIELDS, json_encode($fields)); }
        else { if ($token) $fields['_token'] = $token; curl_setopt($h, CURLOPT_POSTFIELDS, $fields); }
    }
    $b = curl_exec($h); $code = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$code, $b];
}
function tok($html) { preg_match('/name="_token" value="([^"]+)"/', $html, $m); return $m[1] ?? ''; }

// 0. mulai
list($c, $b) = gurl('GET', $base.'/mulai', $cookie);
$t = tok($b);
echo "mulai: $c tok=" . ($t ? substr($t,0,12) : 'MISSING') . "\n";
if (! $t) { file_put_contents('D:/PROJEK/PhotoBooth/storage/app/strip-probe.html', $b); exit; }

// 1. pilih frame fr
list($c, $b) = gurl('POST', $base.'/frame', $cookie, ['frame' => 'fr'], false, $t);
echo "frame: $c\n"; $t = tok($b);

// 2. foto (4 base64)
$photos = [];
for ($i = 1; $i <= 4; $i++) { $photos[] = base64_encode(file_get_contents("$photosDir/foto-$i.jpg")); }
list($c, $b) = gurl('POST', $base.'/foto', $cookie, ['_token' => $t, 'photos' => $photos], true);
echo "foto: $c :: " . substr($b, 0, 110) . "\n";

// 3. filter asli, print copy 1, metode cash, pembayaran
list($c, $b) = gurl('POST', $base.'/filter', $cookie, ['_token' => $t, 'filter' => 'asli'], true);
echo "filter: $c\n";
list($c, $b) = gurl('POST', $base.'/print', $cookie, ['_token' => $t, 'copy' => 1], false);
echo "print: $c\n"; $t = tok($b);
list($c, $b) = gurl('POST', $base.'/metode', $cookie, ['_token' => $t, 'metode' => 'cash'], false);
echo "metode: $c\n"; $t = tok($b);
list($c, $b) = gurl('POST', $base.'/pembayaran', $cookie, ['_token' => $t], true);
echo "pembayaran: $c\n";

// 4. review
list($c, $b) = gurl('GET', $base.'/review', $cookie);
echo "review: $c len=" . strlen($b) . "\n";
preg_match("/data-photos='(.*?)'/s", $b, $m);
echo "photos: " . (isset($m[1]) ? substr($m[1], 0, 220) : 'MISSING') . "\n";
preg_match('/data-frame-image="([^"]*)"/', $b, $m);
echo "frameImage: " . ($m[1] ?? 'MISSING') . "\n";
preg_match("/data-frame-slots='(.*?)'/s", $b, $m);
echo "slots: " . (isset($m[1]) ? substr($m[1], 0, 240) : 'MISSING') . "\n";
file_put_contents('D:/PROJEK/PhotoBooth/storage/app/review-current.html', $b);
echo "SAVED\n";