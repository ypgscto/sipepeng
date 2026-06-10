<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $argv[1] ?? $root.'/public/images/sipepengmaskot_bg.png';
$targets = [
    400 => $root.'/public/images/sipepeng-maskot-400w.png',
    520 => $root.'/public/images/sipepeng-maskot-520w.png',
    780 => $root.'/public/images/sipepeng-maskot-780w.png',
];

if (! extension_loaded('gd')) {
    fwrite(STDERR, "GD extension required.\n");
    exit(1);
}

if (! is_file($source)) {
    fwrite(STDERR, "Source not found: {$source}\n");
    exit(1);
}

$loaded = imagecreatefrompng($source);
if ($loaded === false) {
    fwrite(STDERR, "Cannot read PNG: {$source}\n");
    exit(1);
}

$srcW = imagesx($loaded);
$srcH = imagesy($loaded);

foreach ($targets as $width => $target) {
    $height = (int) round($srcH * ($width / $srcW));

    $canvas = imagecreatetruecolor($width, $height);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);

    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefill($canvas, 0, 0, $transparent);

    imagecopyresampled($canvas, $loaded, 0, 0, 0, 0, $width, $height, $srcW, $srcH);
    imagepng($canvas, $target, 6);
    imagedestroy($canvas);

    printf("Saved %s (%dx%d, %.1f KB)\n", basename($target), $width, $height, filesize($target) / 1024);
}

imagedestroy($loaded);
