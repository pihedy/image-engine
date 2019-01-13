<?php

namespace App\Services;

use PHPImageWorkshop\ImageWorkshop;

class ImageGenerateService
{
    public static function generateImages(array $productData, array $baseImages)
    {
        foreach ($baseImages as $key => $value) {
            if (preg_match("/\_D/i", $key)) {
                self::imagesComposite(
                    'dark',
                    $key
                );
            } else {
                self::imagesComposite(
                    'light',
                    $key
                );
            }
        }
    }

    private static function imagesComposite(string $shadeFolder, string $imageName)
    {
        set_time_limit(0);

        $templateImages = scandir(APP_PATH_ROOT . "/tmp/templates/ferfi_polo/{$shadeFolder}");
        $i = 1;

        foreach ($templateImages as $value) {
            if ($value !== '.' && $value !== '..') {
                $norwayLayer = ImageWorkshop::initFromPath(
                    APP_PATH_ROOT . "/tmp/templates/ferfi_polo/{$shadeFolder}/{$value}"
                );
                $norwayLayer->resizeInPixel(768, null, true);

                $watermarkLayer = ImageWorkshop::initFromPath(
                    APP_PATH_ROOT . "/tmp/base-images/{$imageName}"
                );
                $watermarkLayer->resizeInPixel(200, null, true);

                $norwayLayer->addLayerOnTop($watermarkLayer, 0, -10, "MM");

                $norwayLayer->save(APP_PATH_ROOT . '/tmp/test', "{$imageName}_{$i}.jpg", true, null, 95);
            }

            $i++;
        }
    }
}
