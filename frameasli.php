<?php
$base = 'http://127.0.0.1:8000';
$cookie = 'D:/PROJEK/PhotoBooth/storage/app/frameflow-cookie.txt';
@unlink($cookie);

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
        if ($json) {
            curl_setopt($h, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json', 'X-Requested-With: XMLHttpRequest']);
            curl_setopt($h, CURLOPT_POSTFIELDS, json_encode($fields));
        } else {
            curl_setopt($h, CURLOPT_POSTFIELDS, $fields);
        }
    }
    $b = curl_exec($h); $c = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
    return [$c, $b];
}
function tok($html) { preg_match('/name="_token" value="([^"]+)"/', $html, $m); return $m[1] ?? ''; }

list($c, $b) = rq('GET', $base.'/mulai', $cookie);
$t = tok($b);
echo "mulai: $c tok=" . ($t ? 'ok' : 'MISSING') . "\n";
if (! $t) { file_put_contents('D:/PROJEK/PhotoBooth/storage/app/mulai-probe2.html', $b); exit; }

list($c, $b) = rq('GET', $base.'/frame', $cookie);
echo "frame GET: $c len=" . strlen($b) . "\n";
file_put_contents('D:/PROJEK/PhotoBooth/storage/app/realframe.html', $b);
$cats = preg_match_all('/data-category="([^"]+)"/', $b, $m) ? implode(',', $m[1]) : 'NONE';
echo "categories: $cats\n";
$opts = preg_match_all('/data-frame-option="([^"]+)"/', $b, $m) ? implode(',', $m[1]) : 'NONE';
echo "frame-options: $opts\n";
echo "memiliki router-jump: " . (preg_match('/initFrameSelect|data-frame-form/', $b) ? 'ya' : 'tidak') . "\n";