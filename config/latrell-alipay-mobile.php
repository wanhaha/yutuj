<?php
return [

    // 安全检验码，以数字和字母组成的32位字符。
    'key' => env('ALIPAY_KEY'),

    // 签名方式
    'sign_type' => 'RSA',

    // 商户私钥。
    'private_key_path' => __DIR__ . '/pem/rsa_private_key.pem',

    // 阿里公钥。
    'public_key_path' => __DIR__ . '/pem/rsa_public_key.pem',

    // 异步通知连接。
    'notify_url' => env('ALIPAY_NOTIFY_URL')
];
