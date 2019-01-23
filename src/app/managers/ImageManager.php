<?php

namespace App\Managers;

class ImageManager
{
    public static function compression(string $file, int $quality = 90)
    {
        exec("guetzli --quality {$quality} {$file} {$file}");
    }
}
