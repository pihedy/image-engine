<?php

namespace App\Managers;

class ImageManager
{
    public static function compression(string $folder, int $quality = 90)
    {
        exec("find {$folder} -type f -iname '*.jpg' -exec jpeg-recompress --quality medium --min 50 --method smallfry \{} \{} \;");
        exec("find {$folder} -type f -iname '*.jpg' -exec exiftool -overwrite_original -all= \{} \;");
    }
}
