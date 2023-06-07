<?php
// +----------------------------------------------------------------------
// | tp_wechat配置
// +----------------------------------------------------------------------

return [
    'wechat' => [
        'app_id'  => 'your-app-id',         // AppID
        'secret'  => 'your-app-secret',     // AppSecret
        'token'   => 'your-token',          // Token
        'aes_key' => '',                    // EncodingAESKey，兼容与安全模式下请一定要填写！！！
    ],
    'wechat_pay' => [
        'app_id'  => '',    //AppID
        'mch_id'  => '',    //商户号

        // 商户证书
        'private_key' => '',
        'certificate' => '',

        // v3 API 秘钥
        'secret_key' => '',

        // v2 API 秘钥
        'v2_secret_key' => '',

        // 平台证书：微信支付 APIv3 平台证书，需要使用工具下载
        // 下载工具：https://github.com/wechatpay-apiv3/CertificateDownloader
        // 平台证书需要自动更新，这里设置证书存放目录
        'platform_certs_directory' => '',

        'public_key_pkcs8' => '',

        'notify_url' => '',     //通知接口地址
    ]
];