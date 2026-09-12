<?php
require 'D:/PROJEK/PhotoBooth/vendor/autoload.php';
$app = require 'D:/PROJEK/PhotoBooth/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Storage;

foreach (App\Models\Frame::orderBy('sort_order')->get() as $f) {
    $ok = Storage::disk('public')->exists($f->image_path ?? '');
    echo "#$f->id slug=$f->slug name=".json_encode($f->name)." active=$f->is_active pc=$f->photo_count "
        ."img=".json_encode($f->image_path)." ".($ok?'FILE-OK':'*** FILE-MISSING ***')."\n";
}
echo "--- dir listing ---\n";
print_r(array_map('basename', Storage::disk('public')->files('frames')));