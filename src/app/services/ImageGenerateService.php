<?php

namespace App\Services;

use PHPImageWorkshop\ImageWorkshop;
use App\Managers\ImageManager;
use Amp\Parallel\Worker;
use Amp\Promise;

class ImageGenerateService
{
    public static $galleryArray;

    public static function generateImages(array $productValue, array $baseImages, array $baseProducts)
    {
        $productValue['colors'] = self::cleanColors($productValue['attributes']);
        $productValue['basics'] = self::cleanBasics($productValue['metaData']);
        $productValue['baseIntersect'] = array_intersect_key($baseProducts, array_flip($productValue['basics']));
        $productValue['gallery'] = self::cleanGallery($productValue['metaData']);

        $productValue['insertRequired'] = self::insertRequired($productValue['metaData']);

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
                $productValue['baseIntersect'],
                $productValue['gallery'],
                $productValue['insertRequired']
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

    private static function cleanGallery(array $meta)
    {
        $metaKey = array_search('pamut_design_gallery', array_column($meta, 'key'));

        return $metaKey === false ? [] : $meta[$metaKey]['value'];
    }

    private static function insertRequired(array $meta)
    {
        $metaKey = array_search('isabel_insert_required', array_column($meta, 'key'));

        if ($metaKey === false) {
            $return = false;
        } else if ($meta[$metaKey]['value'] == 'true') {
            $return = true;
        } else {
            $return = false;
        }

        return $return;
    }

    private static function imagesComposite(
        string $shadeFolder, 
        string $imageName, 
        string $designSku, 
        array $baseData, 
        array $gallery,
        bool $insertRequired
    )
    {
        foreach ($baseData as $baseKey => $baseValue) {
            if (!isset($baseValue['colors'])) {
                continue;
            }

            foreach ($baseValue['colors'] as $colorKey => $colorValue) {
                if (($insertRequired === true) && (array_key_exists("{$baseValue['slug']}-{$colorValue}", $gallery))) {
                    continue;
                }

                $scriptLocation = APP_PATH_ROOT . '/scripts/tshirt.sh';
                $templateFile = APP_PATH_ROOT . "/images/base-images/{$baseValue['slug']}/{$shadeFolder}/{$baseValue['slug']}-{$colorValue}.jpg";
                $designFile = APP_PATH_ROOT . "/images/design-images/{$imageName}";
                $exportPath = APP_PATH_ROOT . "/tmp/export/{$baseValue['slug']}";
                $tempPath = APP_PATH_ROOT . "/tmp/{$designSku}";
                $settings = $baseValue['settings'];

                if (file_exists($templateFile)) {
                    $outputFile = "{$tempPath}/{$designSku}-{$baseValue['slug']}-{$colorValue}.jpg";
                    $export = file_exists($exportPath) ? '' : '-E';

                    list($width, $height) = getimagesize($designFile);
                    $rates = self::calculateDesignScale(
                        [
                            'width' => $width,
                            'height' => $height,
                        ],
                        [
                            'width' => $settings['sampleWidth'],
                            'height' => $settings['sampleHeight'],
                        ],
                        [
                            'left' => $settings['shiftLeft'],
                            'top' => $settings['shiftTop']
                        ]
                    );

                    exec(
                        "{$scriptLocation} -r \"{$rates}\" -b 3 -a 0 -A 5 -o 5,0 {$export} -D {$exportPath} {$designFile} {$templateFile} {$outputFile}"
                    );
                }
            }
        }
    }

    private static function calculateDesignScale(array $designRates, array $areaRates, array $shift)
    {
        $actualWidth = round(($designRates['width'] / $designRates['height']) * $areaRates['height']);

        if ($actualWidth >= $areaRates['width']) {
            $rate = "{$areaRates['width']}x{$areaRates['height']}+{$shift['left']}+{$shift['top']}";
        } else {
            $actualLeft = round((($areaRates['width'] - $actualWidth) / 2) + $shift['left']);
            $rate = "{$actualWidth}x{$areaRates['height']}+{$actualLeft}+{$shift['top']}";
        }

        return $rate;
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
                $tempPath = APP_PATH_ROOT . "/tmp/{$productValue['sku']}";
                $designFile = APP_PATH_ROOT . "/images/design-images/{$shadeValue}";
                $outputFile = "{$tempPath}/{$productValue['sku']}-afacebookfeatured-{$mainColor}.jpg";
                $exportPath = APP_PATH_ROOT . "/tmp/export/afacebookfeatured";

                $scriptLocation = APP_PATH_ROOT . '/scripts/tshirt.sh';
                $export = file_exists($exportPath) ? '' : '-E';

                exec(
                    "{$scriptLocation} -r \"847x947+796+184\" -f scale -b 3 -a 0 -A 5 -o 5,0 {$export} -D {$exportPath} {$designFile} {$featuredFile} {$outputFile}"
                );
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
                $tempPath = APP_PATH_ROOT . "/tmp/{$productValue['sku']}";
                $designFile = APP_PATH_ROOT . "/images/design-images/{$shadeValue}";
                $outputFile = "{$tempPath}/{$productValue['sku']}-afeaturedimage-{$mainColor}.jpg";
                $exportPath = APP_PATH_ROOT . "/tmp/export/afeaturedimage";

                $scriptLocation = APP_PATH_ROOT . '/scripts/tshirt.sh';
                $export = file_exists($exportPath) ? '' : '-E';

                exec(
                    "{$scriptLocation} -r \"640x800+80+0\" -f scale -b 3 -a 0 -A 5 -o 5,0 {$export} -D {$exportPath} {$designFile} {$featuredFile} {$outputFile}"
                );
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
