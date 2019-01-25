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

        $files = array_diff(scandir(APP_PATH_ROOT . "/tmp/{$productValue['sku']}"), ['..', '.']);

        foreach ($files as $file) {
            $SpaceConnect->MakePublic(
                "{$friendName}/{$productValue['sku']}/{$file}"
            );
        }
    }
}
