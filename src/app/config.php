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
                'userKey' => 'ck_fbb111d07cef28abc855868515946a6886d0f953',
                'secretKey' => 'cs_009a0f0a290494ffbbc3405dea6a964bb4a9a0e9',
                'options' => [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                ]
            ]
        ],
        'teetakuStaging' => [
            'secretKey' => '123456',
            'domain' => 'https://staging-teetaku.kinsta.com',
            'woocommerce' => [
                'userKey' => 'ck_ebba4d21a3fa0d12c5a0a0f865f75a26fa656cd1',
                'secretKey' => 'cs_723b31c7c2a476cd32c75d8b8c44ea635d56ad2f',
                'options' => [
                    'wp_api' => true,
                    'version' => 'wc/v2',
                ]
            ]
        ]
    ]
];
