<?php

namespace App\Services;

use PHPImageWorkshop\ImageWorkshop;
use App\Managers\ImageManager;

class ThumbnailGenerateService
{
    public static function generateThumbnails(array $productValue, array $thumbnailSettings)
    {
        $files = glob(APP_PATH_ROOT . "/tmp/{$productValue['sku']}/*.jpg");

        foreach ($files as $fileValue) {
            self::generate(
                $productValue['sku'],
                pathinfo($fileValue),
                $thumbnailSettings
            );
        }
    }

    private static function generate(string $folder, array $fileValue, array $thumbnailSettings)
    {
        $tempPath = APP_PATH_ROOT . "/tmp/{$folder}";

        foreach ($thumbnailSettings as $thumbKey => $thumbValue) {
            $layer = ImageWorkshop::initFromPath(
                APP_PATH_ROOT . "/tmp/{$folder}/{$fileValue['basename']}"
            );

            if ($thumbValue['height'] > $thumbValue['width']) {
                $layer->resizeInPixel($thumbValue['height'], $thumbValue['height'], true, 0, 0, 'MM');
                $layer->cropInPixel($thumbValue['width'], $thumbValue['height'], 0, 0, 'MM');
            } elseif ($thumbValue['height'] < $thumbValue['width']) {
                $layer->resizeInPixel($thumbValue['width'], $thumbValue['width'], true, 0, 0, 'MM');
                $layer->cropInPixel($thumbValue['width'], $thumbValue['height'], 0, 0, 'MM');
            } else {
                $layer->resizeInPixel($thumbValue['width'], $thumbValue['height'], true, 0, 0, 'MM');
            }

            $layer->save(
                $tempPath,
                "{$fileValue['filename']}-{$thumbValue['width']}x{$thumbValue['height']}.jpg",
                true,
                null,
                99
            );

            $actualFile = "{$tempPath}/{$fileValue['filename']}-{$thumbValue['width']}x{$thumbValue['height']}.jpg";
        }
    }
}
