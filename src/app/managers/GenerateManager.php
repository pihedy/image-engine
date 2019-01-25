<?php

namespace App\Managers;

use App\Services\ImageGenerateService;
use App\Services\ThumbnailGenerateService;
use App\Services\ImageUploadService;
use App\Services\ApiDispatcherService;

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
        $this->settings = $settings;

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
        $host = $this->settings['friends'][$this->friendName]['domain'];

        foreach ($this->productOrigin as $productKey => $productValue) {
            try {
                ImageGenerateService::generateImages(
                    $productValue, 
                    $this->baseImages, 
                    $this->baseProductSettings
                );

                ApiDispatcherService::postInProgress(
                    $host,
                    $productKey,
                    'All images generated.'
                );
    
                ThumbnailGenerateService::generateThumbnails(
                    $productValue,
                    $this->thumbnailSettings
                );

                ApiDispatcherService::postInProgress(
                    $host,
                    $productKey,
                    'All thumbnails generated.'
                );
    
                ImageUploadService::uploadFiles(
                    $productValue,
                    $this->SpaceConnect,
                    $this->friendName
                );

                ApiDispatcherService::postInProgress(
                    $host,
                    $productKey,
                    'All the files were uploaded to the cloud.',
                    201
                );
            } catch (\Exception $e) {
                ApiDispatcherService::postInProgress(
                    $host,
                    $productKey,
                    $e->getMessage(),
                    $e->getCode()
                );

                continue;
            }
        }
    }
}
