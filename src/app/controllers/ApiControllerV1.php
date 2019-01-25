<?php

namespace App\Controllers;

use App\Lib\Controller;
use App\Managers\GenerateManager;
use App\Services\GoogleClientService;
use App\Services\SpaceService;
use App\Services\ApiDispatcherService;
use Automattic\WooCommerce\Client;

/**
 * Api v1 controller class.
 */
class ApiControllerV1 extends Controller
{
    /**
     * generateImages
     * @param mixed $request 
     * @param mixed $response 
     * @return mixed 
     */
    public function generateImages($request, $response)
    {
        /* Default status code. */
        $statusCode = 201;

        /* Getting data from the request. */
        $requestBody = $request->getParsedBody();

        if (!isset($this->container->get('settings')['friends'][$requestBody['host']])) {
            return $response->withStatus(400);
        } else {
            $friendData = $this->container->get('settings')['friends'][$requestBody['host']];
        }

        try {
            $woocommerce = new Client(
                $friendData['domain'],
                $friendData['woocommerce']['userKey'],
                $friendData['woocommerce']['secretKey'],
                $friendData['woocommerce']['options']
            );
    
            $productOrigin = [];
    
            if (is_array($requestBody['products'])) {
                foreach ($requestBody['products'] as $value) {
                    $data = $woocommerce->get("products/{$value}");
    
                    $productOrigin[$value] = [
                        'sku' => $data['sku'],
                        'attributes' => $data['attributes'],
                        'metaData' => $data['meta_data'],
                    ];

                    ApiDispatcherService::postInProgress(
                        $friendData['domain'],
                        $value,
                        'WooCommerce data downloaded.'
                    );
                }
            }
    
            $service = new \Google_Service_Drive(
                GoogleClientService::getClient()
            );
    
            foreach ($productOrigin as $key => $value) {
                foreach ($this->container->get('settings')['imageShades'] as $shadeKey => $shadeValue) {
                    $productOrigin[$key]['imageShades'][$shadeKey] = $value['sku'] . $shadeValue . ".png";
                }
            }
    
            $baseImages = [];
    
            foreach ($productOrigin as $key => $value) {
                $dark = $value['imageShades']['dark'];
                $light = $value['imageShades']['light'];
    
                $gDriveResponse = $service->files->listFiles([
                    'q' => "mimeType='image/png' and name='{$dark}' or name='{$light}'",
                    'spaces' => 'drive',
                    'fields' => 'nextPageToken, files(id, name)',
                ]);
    
                foreach ($gDriveResponse->files as $file) {
                    $baseImages[$file->name] = $file->id;

                    ApiDispatcherService::postInProgress(
                        $friendData['domain'],
                        $key,
                        "$file->name image founded and downloaded. (ID: $file->id)."
                    );
                }
            }
    
            foreach ($baseImages as $key => $value) {
                $content = $service->files->get(
                    $value,
                    [
                        'alt' => 'media'
                    ]
                );
    
                file_put_contents(
                    APP_PATH_ROOT . "/images/design-images/{$key}",
                    $content->getBody()->getContents()
                );
            }
    
            $GenerateManager = new GenerateManager(
                $productOrigin,
                $baseImages,
                $requestBody['host'],
                $this->container->get('settings')
            );
    
            $GenerateManager->run();
        } catch (\Exception $e) {
            return $response->withJson([
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\TypeError $e) {
            return $response->withJson([
                'message' => $e->getMessage()
            ], 500);
        }

        return $response->withJson([
            'message' => 'ok'
        ], $statusCode);
    }

    /**
     * setBaseProducts
     * @param mixed $request 
     * @param mixed $response 
     * @return mixed 
     */
    public function setBaseProducts($request, $response)
    {
        /* Default status code. */
        $statusCode = 201;

        /* Getting data from the request. */
        $requestBody = $request->getParsedBody();

        if (!isset($this->container->get('settings')['friends'][$requestBody['host']])) {
            return $response->withStatus(400);
        } else {
            /* Getting friend data. */
            $friendData = $this->container->get('settings')['friends'][$requestBody['host']];
        }

        try {
            $woocommerce = new Client(
                $friendData['domain'],
                $friendData['woocommerce']['userKey'],
                $friendData['woocommerce']['secretKey'],
                $friendData['woocommerce']['options']
            );
    
            $productOrigin = [];
    
            foreach ($requestBody['base_products'] as $requestKey => $requestValue) {
                $productData = $woocommerce->get("products/{$requestKey}");
                $slug = str_replace('-', '_', $productData['slug']);
                $productVatiations = [];
                $page = 1;


                do {
                    $parameters = [
                        'per_page' => 99,
                        'page' => $page
                    ];

                    $vatiations = $woocommerce->get("products/{$requestKey}/variations", $parameters);

                    if (sizeof($vatiations) >= 99) {
                        $page++;
                        $retry = true;
                    } else {
                        $retry = false;
                    }

                    $productVatiations = array_merge($productVatiations, $vatiations);

                } while ($retry);
                
                $productOrigin[$requestKey]['slug'] = $slug;
                $productOrigin[$requestKey]['settings'] = $requestValue;
    
                $iterator = 'none';
    
                foreach ($productVatiations as $key => $value) {
                    if ($value['purchasable'] === false) {
                        continue;
                    }
    
                    parse_str(parse_url($value['permalink'], PHP_URL_QUERY), $query);
    
                    if (isset($query['attribute_pa_szin']) && ($query['attribute_pa_szin'] != $iterator)) {
                        $productOrigin[$requestKey]['colors'][] = $query['attribute_pa_szin'];
                        $iterator = $query['attribute_pa_szin'];
                    }
                }
            }
    
            if (!file_exists(APP_PATH_ROOT . "/settings/{$requestBody['host']}")) {
                mkdir(
                    APP_PATH_ROOT . "/settings/{$requestBody['host']}",
                    0755,
                    true
                );
            }
    
            file_put_contents(
                APP_PATH_ROOT . "/settings/{$requestBody['host']}/base-products.json",
                json_encode($productOrigin)
            );
        } catch (\Exception $e) {
            return $response->withJson([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return $response->withJson([
            'message' => 'ok'
        ], $statusCode);
    }

    /**
     * setThumbnailSettings
     * @param mixed $request 
     * @param mixed $response 
     * @return mixed 
     */
    public function setThumbnailSettings($request, $response)
    {
        /* Default status code. */
        $statusCode = 201;

        /* Getting data from the request. */
        $requestBody = $request->getParsedBody();

        if (!isset($this->container->get('settings')['friends'][$requestBody['host']])) {
            return $response->withJson(400);
        } else {
            /* Getting friend data. */
            $friendData = $this->container->get('settings')['friends'][$requestBody['host']];
        }

        try {
            if (!file_exists(APP_PATH_ROOT . "/settings/{$requestBody['host']}")) {
                mkdir(
                    APP_PATH_ROOT . "/settings/{$requestBody['host']}",
                    0755,
                    true
                );
            }
    
            file_put_contents(
                APP_PATH_ROOT . "/settings/{$requestBody['host']}/thumbnail-settings.json",
                json_encode($requestBody['thumbs'])
            );
        } catch (\Exception $e) {
            return $response->withJson([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return $response->withJson([
            'message' => 'ok'
        ], $statusCode);
    }
}
