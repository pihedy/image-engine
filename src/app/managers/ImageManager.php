<?php

namespace App\Managers;

class ImageManager
{
    public static function compression(string $folder, int $quality = 90)
    {
        exec("caesiumclt -q {$quality} -R -o {$folder} {$folder}");
    }
}
