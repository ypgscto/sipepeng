<?php

declare(strict_types=1);

$source = $argv[1] ?? dirname(__DIR__).'/public/images/sipepeng-mascot-source.png';
$target = $argv[2] ?? dirname(__DIR__).'/public/images/sipepeng-mascot.png';
$threshold = (int) ($argv[3] ?? 55);

if (! extension_loaded('gd')) {
    fwrite(STDERR, "GD extension required.\n");
    exit(1);
}

function loadImage(string $path)
{
    $bytes = file_get_contents($path, false, null, 0, 12);
    if ($bytes === false) {
        return false;
    }

    if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
        return imagecreatefromjpeg($path);
    }

    if (str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
        return imagecreatefrompng($path);
    }

    if (str_starts_with($bytes, 'RIFF') && str_contains($bytes, 'WEBP')) {
        return imagecreatefromwebp($path);
    }

    return false;
}

$loaded = loadImage($source);
if ($loaded === false) {
    fwrite(STDERR, "Cannot read image: {$source}\n");
    exit(1);
}

$width = imagesx($loaded);
$height = imagesy($loaded);

$image = imagecreatetruecolor($width, $height);
imagesavealpha($image, true);
imagealphablending($image, false);

$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparent);

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgba = imagecolorat($loaded, $x, $y);
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;

        if ($r <= $threshold && $g <= $threshold && $b <= $threshold) {
            imagesetpixel($image, $x, $y, $transparent);
            continue;
        }

        $color = imagecolorallocatealpha($image, $r, $g, $b, 0);
        imagesetpixel($image, $x, $y, $color);
    }
}

imagedestroy($loaded);
imagepng($image, $target, 6);
imagedestroy($image);

echo "Saved transparent PNG to {$target}\n";
