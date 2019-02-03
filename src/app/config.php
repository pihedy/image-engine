<?php

return [
    'secretKey' => 'asdf123',
    'imageShades' => [
        'dark' => '_D',
        'light' => '_L',
    ],
    'space' => [
        'key' => 'W5TRVTDB2CBPZF4J5VSE',
        'secret'=> 'ReJWjp5O1vLY79d1DcloUXqeJKwafYz6Qud1oguLJrM',
        'name' => 'isabel',
        'region' => 'ams3'
    ],
    'friends' => [
        'pamutlabor' => [],
        'playground' => [
            'secretKey' => 'asdf123',
            'domain' => 'http://playground.io',
            'woocommerce' => [
                'userKey' => 'ck_822a7253c95fa4da68f6496c0a53530ef1422c41',
                'secretKey' => 'cs_f5da4a8e5a479ea29157f0418a50f943a1b0ed93',
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
