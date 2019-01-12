<?php

// Api
$app->group('/api', function () {

    // V1
    $this->group('/v1', function () {

        // Order create
        $this->post(
            '/product',
            'ApiControllerV1:postProduct'
        );

    });

});
