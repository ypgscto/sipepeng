<?php

$files = glob(dirname(__DIR__).'/public/images/*{mascot,maskot}*.png', GLOB_BRACE) ?: [];

foreach ($files as $file) {
    $size = @getimagesize($file);
    if ($size === false) {
        continue;
    }

    printf(
        "%s: %dx%d, %.2f MB\n",
        basename($file),
        $size[0],
        $size[1],
        filesize($file) / 1024 / 1024
    );
}
