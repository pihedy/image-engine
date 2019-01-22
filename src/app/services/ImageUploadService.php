<?php

namespace App\Services;

use function Aws\serialize;


class ImageUploadService
{   
    public static function uploadFiles(array $productValue, $SpaceConnect)
    {
        $SpaceConnect->UploadDirectory(
            APP_PATH_ROOT . "/tmp/{$productValue['sku']}",
            $productValue['sku']
        );
    }
}
