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
                'userKey' => 'ck_854687b5115eeb9c214c6e3e091d893f9b61fe78',
                'secretKey' => 'cs_baa1c56396a97f50c5356f334c1ae3972af6949f',
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
