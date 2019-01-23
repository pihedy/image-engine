<?php

namespace App\Managers;

use App\Services\ImageGenerateService;
use App\Services\ThumbnailGenerateService;
use App\Services\ImageUploadService;

class GenerateManager 
{
    private $productOrigin;

    private $baseImages;

    private $friendName;

    private $settings;

    private $baseProductSettings;

    private $thumbnailSettings;

    private $SpaceConnect;

    public function __construct(array $productOrigin, array $baseImages, string $friendName, $settings)
    {
        $this->productOrigin = $productOrigin;
        $this->baseImages = $baseImages;
        $this->friendName = $friendName;

        $this->baseProductSettings = json_decode(
            file_get_contents(APP_PATH_ROOT . "/settings/{$friendName}/base-products.json"),
            true
        );

        $this->thumbnailSettings = json_decode(
            file_get_contents(APP_PATH_ROOT . "/settings/{$friendName}/thumbnail-settings.json"),
            true
        );

        $this->SpaceConnect = new \SpacesConnect(
            $settings['space']['key'],
            $settings['space']['secret'],
            $settings['space']['name'],
            $settings['space']['region']
        );
    }

    public function run()
    {
        foreach ($this->productOrigin as $productKey => $productValue) {
            ImageGenerateService::generateImages(
                $productValue, 
                $this->baseImages, 
                $this->baseProductSettings
            );

            ThumbnailGenerateService::generateThumbnails(
                $productValue,
                $this->thumbnailSettings
            );

            /* ImageUploadService::uploadFiles(
                $productValue,
                $this->SpaceConnect
            ); */
        }
    }
}
