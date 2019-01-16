<?php

namespace App\Services;

use App\Managers\FilesystemManager;
use PHPImageWorkshop\ImageWorkshop;

class ThumbnailGenerateService
{
    public static function generateThumbnails(array $productData, string $friendName)
    {
        $thumbnailSettings = json_decode(
            file_get_contents(APP_PATH_ROOT . "/settings/{$friendName}/thumbnail-settings.json"),
            true
        );

        foreach ($productData as $productKey => $productValue) {
            $files = glob(APP_PATH_ROOT . "/tmp/{$productValue['sku']}/*.jpg");

            foreach ($files as $fileValue) {
                self::generate(
                    $productValue['sku'],
                    pathinfo($fileValue),
                    $thumbnailSettings
                );
            }
        }
    }

    private static function generate(string $folder, array $fileValue, array $thumbnailSettings)
    {
        set_time_limit(0);

        foreach ($thumbnailSettings as $thumbKey => $thumbValue) {
            $layer = ImageWorkshop::initFromPath(
                APP_PATH_ROOT . "/tmp/{$folder}/{$fileValue['basename']}"
            );

            $layer->resizeInPixel(
                $thumbValue['width'],
                $thumbValue['height'],
                true,
                0,
                0,
                'MM'
            );

            $layer->save(
                APP_PATH_ROOT . "/tmp/{$folder}",
                "{$fileValue['filename']}-{$thumbValue['width']}x{$thumbValue['height']}.jpg",
                true,
                null,
                95
            );
        }
    }
}
