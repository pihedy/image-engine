<?php

/* API group. */
$app->group('/api', function () {

    /* API v1. */
    $this->group('/v1', function () {

        /* Create design product images. */
        $this->post(
            '/generate_images',
            'ApiControllerV1:generateImages'
        );

        /* Create base product settings file. */
        $this->post(
            '/set_base_products',
            'ApiControllerV1:setBaseProducts'
        );

        /* Create thumbnail settings file. */
        $this->post(
            '/set_thumbnail_settings',
            'ApiControllerV1:setThumbnailSettings'
        );

    });

});
