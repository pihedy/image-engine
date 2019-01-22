<?php

return [
    'secretKey' => 'asdf123',
    'imageShades' => [
        'dark' => '_D',
        'light' => '_L',
    ],
    'space' => [
        'key' => 'PEI4PBQQQDBWQ23KXCOT',
        'secret'=> 'Bk0Ta0nVJ/TdNJjIoL0ACRiz8MdoZMKBJq3D4/epwmY',
        'name' => 'isabel',
        'region' => 'ams3'
    ],
    'friends' => [
        'pamutlabor' => [],
        'playground' => [
            'secretKey' => 'asdf123',
            'domain' => 'http://playground.io',
            'woocommerce' => [
                'userKey' => 'ck_0caad4052a4fb92fafa5321819340d70624b892f',
                'secretKey' => 'cs_754504bf20c402a0bbbb2aa811a22c2712ae208f',
                'options' => [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                ]
            ]
        ],
        'pamutlaborStaging' => [
            'secretKey' => '123456',
            'domain' => 'https://staging-pamutlabor.kinsta.com',
            'woocommerce' => [
                'userKey' => 'ck_92f33d8238c83b02bb2d01e50f876a18e89658d5',
                'secretKey' => 'cs_0975a0f55b9d2a2e23df23c44a03f1c65246b322',
                'options' => [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                ]
            ]
        ]
    ]
];
