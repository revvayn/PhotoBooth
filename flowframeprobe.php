<?php
$base = 'http://127.0.0.1:8000';
$cookie = 'D:/PROJEK/PhotoBooth/storage/app/fr3-cookie.txt';
@unlink($cookiedies);

function rq($meth, $url, $cookie, $fields = null, $json = false) {
    $h = curl_init($url);
    if ($meth === 'GET') {
        curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($h, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    } else {
        curl_setopt($h, CURLOPT_POST, true);
        curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($h, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($h, CURLOPT_COOKIEFILE, $cookie);
    }
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b];
}
function tok($html) { preg_match('/name="_token" value="([^"]+)"/', $html, $m); return $m[1] ?? ''; }
function post($url, $cookie, $fields) {
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

// 1. home -> token
list($c, $b) = rq('GET', $base.'/', $cookie);
$t = tok($b);
echo "home: $c tok=" . ($t ? 'ok' : 'MISSING') . "\n";
if (! $t) { file_put_contents('D:/PROJEK/PhotoBooth/storage/app/home-probe.html', $b); exit; }

// 2. POST /mulai -> queue session
list($c, $b) = post($base.'/mulai', $cookie, ['_token' => $t]);
echo "mulai: $c\n";

// 3. GET /frame -> halaman pilih frame
list($c, $b) = rq('GET', $base.'/frame', $cookie);
echo "frame: $c len=" . strlen($b) . "\n";
file_put_contents('D:/PROJEK/PhotoBooth/storage/app/realframe-clean.html', $b);

preg_match_all('/data-category="([^"]+)"/', $b, $m); echo "cats(dq): " . implode(',', array_unique($m[1])) . "\n";
preg_match_all("/data-category='([^']+)'/", $b, $m); echo "cats(sq): " . implode(',', array_unique($m[1])) . "\n";
preg_match_all('/data-frame-option="([^"]+)"/', $b, $m); echo "opts: " . implode(',', $m[1]) . "\n";
preg_match('/data-frame-slots=/', $b, $m); echo "slots-attr: " . ($m ? 'yes' : 'no') . "\n";