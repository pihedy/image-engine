<?php

namespace App\Services;

class ImageUploadService
{   
    public static function uploadFiles(array $productValue, $SpaceConnect, string $friendName)
    {
        $SpaceConnect->UploadDirectory(
            APP_PATH_ROOT . "/tmp/{$productValue['sku']}",
            "{$friendName}/{$productValue['sku']}"
        );
    }
}
