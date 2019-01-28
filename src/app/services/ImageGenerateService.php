<?php

namespace App\Services;

use PHPImageWorkshop\ImageWorkshop;
use App\Managers\ImageManager;
use Amp\Parallel\Worker;
use Amp\Promise;

class ImageGenerateService
{
    public static function generateImages(array $productValue, array $baseImages, array $baseProducts)
    {
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

            $counter = 1;
            foreach ($baseValue['colors'] as $colorKey => $colorValue) {
                $scriptLocation = APP_PATH_ROOT . '/scripts/tshirt.sh';
                $templateFile = APP_PATH_ROOT . "/images/base-images/{$baseValue['slug']}/{$shadeFolder}/{$baseValue['slug']}-{$colorValue}.jpg";
                $designFile = APP_PATH_ROOT . "/images/design-images/{$imageName}";
                $tempPath = APP_PATH_ROOT . "/tmp/{$designSku}";
                $settings = $baseValue['settings'];

                $actualOption = "{$settings['sampleWidth']}x{$settings['sampleHeight']}+{$settings['shiftLeft']}+{$settings['shiftTop']}";

                if (file_exists($templateFile)) {
                    $outputFile = "{$tempPath}/{$designSku}-{$baseValue['slug']}-{$colorValue}.jpg";

                    $export = $counter === 1 ? '-E' : '';

                    exec(
                        "{$scriptLocation} -r \"{$actualOption}\" -o 5,0 {$export} {$designFile} {$templateFile} {$outputFile}"
                    );

                    $actualFile = "{$tempPath}/{$designSku}-{$baseValue['slug']}-{$colorValue}.jpg";

                    /* ImageManager::compression(
                        $actualFile
                    ); */

                    $counter++;
                }
            }
        }
    }

    public static function generateFacebookFeatured(array $productValue, array $baseImages)
    {
        $mainColor = self::getMainColor($productValue['metaData']);

        foreach ($productValue['imageShades'] as $shadeKey => $shadeValue) {
            if (!array_key_exists($shadeValue, $baseImages)) {
                continue;
            }

            $featuredFile = APP_PATH_ROOT . "/images/base-images/afacebookfeatured/{$shadeKey}/afacebookfeatured-{$mainColor}.jpg";

            if (file_exists($featuredFile)) {
                $norwayLayer = ImageWorkshop::initFromPath($featuredFile);
    
                $watermarkLayer = ImageWorkshop::initFromPath(APP_PATH_ROOT . "/images/design-images/{$shadeValue}");
                $watermarkLayer->resizeInPixel(500, null, true);
    
                $norwayLayer->addLayerOnTop($watermarkLayer, 0, 0, "MM");
                $norwayLayer->save(APP_PATH_ROOT . "/tmp/{$productValue['sku']}", "{$productValue['sku']}-afacebookfeatured-{$mainColor}.jpg", true, null, 90);
            }
        }
    }

    public static function generateFeatured(array $productValue, array $baseImages)
    {
        $mainColor = self::getMainColor($productValue['metaData']);

        foreach ($productValue['imageShades'] as $shadeKey => $shadeValue) {
            if (!array_key_exists($shadeValue, $baseImages)) {
                continue;
            }

            $featuredFile = APP_PATH_ROOT . "/images/base-images/afeaturedimage/{$shadeKey}/afeaturedimage-{$mainColor}.jpg";

            if (file_exists($featuredFile)) {
                $norwayLayer = ImageWorkshop::initFromPath($featuredFile);
    
                $watermarkLayer = ImageWorkshop::initFromPath(APP_PATH_ROOT . "/images/design-images/{$shadeValue}");
                $watermarkLayer->resizeInPixel(300, null, true);
    
                $norwayLayer->addLayerOnTop($watermarkLayer, 0, 0, "MM");
                $norwayLayer->save(APP_PATH_ROOT . "/tmp/{$productValue['sku']}", "{$productValue['sku']}-afeaturedimage-{$mainColor}.jpg", true, null, 90);
            }
        }
    }

    private static function getMainColor(array $metaData)
    {
        $metaKey = array_search('_isabel_main_variable', array_column($metaData, 'key'));

        return $metaData[$metaKey]['value'];
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
