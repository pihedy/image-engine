<?php

namespace App\Services;

use PHPImageWorkshop\ImageWorkshop;

class ImageGenerateService
{
    public static function generateImages(array $productData, array $baseImages, string $friendName)
    {
        foreach ($productData as $productKey => $productValue) {
            $productData[$productKey]['colors'] = self::cleanColors($productValue['attributes']);
            $productData[$productKey]['basics'] = self::cleanBasics($productValue['metaData']);

            unset($productData[$productKey]['attributes']);
            unset($productData[$productKey]['metaData']);
        }

        $baseData = json_decode(
            file_get_contents(APP_PATH_ROOT . "/settings/{$friendName}/base-products.json"),
            true
        );

        foreach ($productData as $key => $value) {
            $productData[$key]['baseIntersect'] = array_intersect_key($baseData, array_flip($value['basics']));

            unset($productData[$key]['basics']);
        }

        foreach ($productData as $productKey => $productValue) {
            foreach ($productValue['baseIntersect'] as $baseKey => $baseValue) {
                if (!isset($baseValue['colors'])) {
                    continue;
                }

                foreach ($baseValue['colors'] as $key => $value) {
                    if (!in_array($value, $productValue['colors'])) {
                        unset($productData[$productKey][$baseKey][$key]);
                    }
                }
            }
        }

        foreach ($productData as $productKey => $productValue) {
            if (!file_exists(APP_PATH_ROOT . "/tmp/{$productValue['sku']}")) {
                mkdir(
                    APP_PATH_ROOT . "/tmp/{$productValue['sku']}",
                    0755,
                    true
                );
            }

            foreach ($productValue['imageShades'] as $imageKey => $imageValue) {
                self::imagesComposite(
                    $imageKey,
                    $imageValue,
                    $productValue['sku'],
                    $productValue['baseIntersect']
                );
            }
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
            foreach ($baseValue['colors'] as $colorKey => $colorValue) {
                $templateFile = APP_PATH_ROOT . "/images/base-images/{$baseValue['slug']}/{$shadeFolder}/{$baseValue['slug']}-{$colorValue}.jpg";

                if (file_exists($templateFile)) {
                    $norwayLayer = ImageWorkshop::initFromPath($templateFile);
                    $norwayLayer->resizeInPixel(768, null, true);

                    $watermarkLayer = ImageWorkshop::initFromPath(APP_PATH_ROOT . "/images/design-images/{$imageName}");
                    $watermarkLayer->resizeInPixel(200, null, true);

                    $norwayLayer->addLayerOnTop($watermarkLayer, 0, 0, "MM");
                    $norwayLayer->save(APP_PATH_ROOT . "/tmp/{$designSku}", "{$baseValue['slug']}-{$colorValue}.jpg", true, null, 70);
                }
            }
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
