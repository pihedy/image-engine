<?php

namespace App\Services;

use PHPImageWorkshop\ImageWorkshop;

class ImageGenerateService
{
    public static function generateImages(array $productValue, array $baseImages, array $baseProducts)
    {
        $product = $productValue;

        $productValue['colors'] = self::cleanColors($productValue['attributes']);
        $productValue['basics'] = self::cleanBasics($productValue['metaData']);
        $productValue['baseIntersect'] = array_intersect_key($baseProducts, array_flip($productValue['basics']));

        foreach ($productValue['baseIntersect'] as $baseKey => $baseValue) {
            if (!isset($baseValue['colors'])) {
                continue;
            }

            foreach ($baseValue['colors'] as $key => $value) {
                if (!in_array($value, $productValue['colors'])) {
                    unset($productValue[$baseKey][$key]);
                }
            }
        }

        if (!file_exists(APP_PATH_ROOT . "/tmp/{$productValue['sku']}")) {
            mkdir(
                APP_PATH_ROOT . "/tmp/{$productValue['sku']}",
                0755,
                true
            );
        }

        foreach ($productValue['imageShades'] as $imageKey => $imageValue) {
            if (!array_key_exists($imageValue, $baseImages)) {
                continue;
            }

            self::imagesComposite(
                $imageKey,
                $imageValue,
                $productValue['sku'],
                $productValue['baseIntersect']
            );
        }
    }

    private static function cleanColors(array $attributes)
    {
        $colorKey = array_search('Szín', array_column($attributes, 'name'));
        $return = [];

        foreach ($attributes[$colorKey]['options'] as $key => $value) {
            $return[] = self::normalize(
                strtolower(str_replace(' ', '_', $value))
            );
        }

        return $return;
    }

    private static function cleanBasics(array $meta)
    {
        $metaKey = array_search('pamut_design', array_column($meta, 'key'));
        $return = $meta[$metaKey]['value']['base'];

        return $return;
    }

    private static function imagesComposite(string $shadeFolder, string $imageName, string $designSku, array $baseData)
    {
        set_time_limit(0);

        foreach ($baseData as $baseKey => $baseValue) {
            if (!isset($baseValue['colors'])) {
                continue;
            }

            foreach ($baseValue['colors'] as $colorKey => $colorValue) {
                $templateFile = APP_PATH_ROOT . "/images/base-images/{$baseValue['slug']}/{$shadeFolder}/{$baseValue['slug']}-{$colorValue}.jpg";

                if (file_exists($templateFile)) {
                    $norwayLayer = ImageWorkshop::initFromPath($templateFile);
                    $norwayLayer->resizeInPixel(1000, null, true);

                    $watermarkLayer = ImageWorkshop::initFromPath(APP_PATH_ROOT . "/images/design-images/{$imageName}");
                    $watermarkLayer->resizeInPixel(200, null, true);

                    $norwayLayer->addLayerOnTop($watermarkLayer, 0, 0, "MM");
                    $norwayLayer->save(APP_PATH_ROOT . "/tmp/{$designSku}", "{$designSku}-{$baseValue['slug']}-{$colorValue}.jpg", true, null, 95);
                }

                self::generateFacebookFeatured(
                    $designSku,
                    $imageName,
                    $shadeFolder,
                    $colorValue
                );

                self::generateFeatured(
                    $designSku,
                    $imageName,
                    $shadeFolder,
                    $colorValue
                );
            }
        }
    }

    private static function generateFacebookFeatured(string $designSku, string $imageName, string $shadeFolder, string $color)
    {
        $featuredFile = APP_PATH_ROOT . "/images/base-images/afacebookfeatured/{$shadeFolder}/afacebookfeatured-{$color}.jpg";

        if (file_exists($featuredFile)) {
            $norwayLayer = ImageWorkshop::initFromPath($featuredFile);

            $watermarkLayer = ImageWorkshop::initFromPath(APP_PATH_ROOT . "/images/design-images/{$imageName}");
            $watermarkLayer->resizeInPixel(500, null, true);

            $norwayLayer->addLayerOnTop($watermarkLayer, 0, 0, "MM");
            $norwayLayer->save(APP_PATH_ROOT . "/tmp/{$designSku}/afacebookfeatured", "afacebookfeatured-{$color}.jpg", true, null, 95);
        }
    }

    private static function generateFeatured(string $designSku, string $imageName, string $shadeFolder, string $color)
    {
        $featuredFile = APP_PATH_ROOT . "/images/base-images/afeaturedimage/{$shadeFolder}/afeaturedimage-{$color}.jpg";

        if (file_exists($featuredFile)) {
            $norwayLayer = ImageWorkshop::initFromPath($featuredFile);

            $watermarkLayer = ImageWorkshop::initFromPath(APP_PATH_ROOT . "/images/design-images/{$imageName}");
            $watermarkLayer->resizeInPixel(200, null, true);

            $norwayLayer->addLayerOnTop($watermarkLayer, 0, 0, "MM");
            $norwayLayer->save(APP_PATH_ROOT . "/tmp/{$designSku}/afeaturedimage", "afeaturedimage-{$color}.jpg", true, null, 95);
        }
    }

    private static function normalize(string $string)
    {
        $normalizeChars = [
            'É' => 'E', 'Á' => 'A', 'Ű' => 'U',
            'Ü' => 'U', 'Ú' => 'U', 'Ő' => 'O',
            'Ö' => 'O', 'Ó' => 'O', 'Í' => 'I',
            'é' => 'e', 'á' => 'a', 'ű' => 'u',
            'ü' => 'u', 'ú' => 'u', 'ő' => 'o',
            'ö' => 'o', 'ó' => 'o', 'í' => 'i',
        ];

        return strtr($string, $normalizeChars);
    }
}
