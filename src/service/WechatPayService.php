<?php

namespace Yuyue8\TpWechat\service;

use EasyWeChat\Pay\Application;
use EasyWeChat\Pay\Client;
use EasyWeChat\Pay\Message;
use EasyWeChat\Pay\Server;
use EasyWeChat\Pay\Utils;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use think\facade\Config;
use Yuyue8\TpWechat\interfaces\WechatPayInterface;
use Yuyue8\TpWechat\utils\FileUtil;

/**微信支付
 * Class WechatPayService
 * @package service
 */
class WechatPayService implements WechatPayInterface
{
    /**
     * @var Application
     */
    protected $instance;

    protected $client;

    protected $utils;

    protected $config;

    protected $platform_certs;

    public function __construct()
    {
        $config = Config::get('tp_wechat.wechat_pay');

        $config['platform_certs'] = $this->getPlatformCertsList($config);

        $this->config = $config;
    }

    /**
     * 设置配置
     *
     * @param array $config
     * @return array
     */
    public function setConfig(array $config) : array
    {
        $config['platform_certs'] = $this->getPlatformCertsList($config);

        $this->config   = $config;

        return $config;
    }
    
    /**
     * 初始化
     * @param bool $cache
     * @return Application
     */
    public function application()
    {
        !isset($this->instance[$this->config['mch_id']]) && ($this->instance[$this->config['mch_id']] = new Application($this->config));
        return $this->instance[$this->config['mch_id']];
    }

    /**
     * 
     *
     * @return Client
     */
    public function client()
    {
        !isset($this->client[$this->config['mch_id']]) && ($this->client[$this->config['mch_id']] = $this->application()->getClient());
        return $this->client[$this->config['mch_id']];
    }

    /**
     * 微信支付工具
     *
     * @return Utils
     */
    public function utils()
    {
        !isset($this->utils[$this->config['mch_id']]) && ($this->utils[$this->config['mch_id']] = $this->application()->getUtils());
        return $this->utils[$this->config['mch_id']];
    }

    /**
     * 微信支付服务
     */
    public function server() : ResponseInterface
    {
        $request = request();
        $symfony_request = new HttpFoundationRequest($request->get(), $request->post(), [], $request->cookie(), [], [], $request->getContent());
        $symfony_request->headers = new HeaderBag($request->header());

        $wechat = $this->application();
        $wechat->setRequestFromSymfonyRequest($symfony_request);
        $server = $wechat->getServer();

        //成功状态 SDK 默认会返回 success, 你可以不用返回任何东西；
        // 默认返回 ['code' => 'SUCCESS', 'message' => '成功']
        return $this->hook($server)->serve();
    }

    /**
     * 监听行为
     *
     * @param Server $server
     * @return Server
     */
    private function hook(Server $server)
    {
        //支付成功事件
        $server->handlePaid(function(Message $message, \Closure $next){
            // $message->out_trade_no 获取商户订单号
            // $message->payer['openid'] 获取支付者 openid
            // 🚨🚨🚨 注意：推送信息不一定靠谱哈，请务必验证
            // 建议是拿订单号调用微信支付查询接口，以查询到的订单状态为准

            $this->payNotice($message);

            return $next($message);
        });

        //处理退款结果事件
        $server->handleRefunded(function(Message $message, \Closure $next){
            // $message->out_trade_no 获取商户订单号
            // $message->payer['openid'] 获取支付者 openid

            $this->refundNotice($message);

            return $next($message);
        });

        return $server;
    }

    /**
     * 订单支付通知
     *
     * @param Message $message
     * @return void
     */
    protected function payNotice(Message $message)
    {
    }

    /**
     * 退款通知
     *
     * @param Message $message
     * @return void
     */
    protected function refundNotice(Message $message)
    {
    }

    /**
     * 获取平台证书列表
     *
     * @param string $out_refund_no
     * @return array
     */
    public function getPlatformCerts()
    {
        $response = $this->client()->get('v3/certificates')->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['status']];
        }

        return [true, $response->toArray()];
    }

    /**
     * jsapi下单
     *
     * @param string $out_trade_no 订单号
     * @param string $time_expire 过期时间
     * @param float $money 金额
     * @param string $openid openid
     * @param string $description 商品描述
     * @param array $attach 附加数据，在查询API和支付通知中原样返回
     * @return array
     */
    public function jsapiOrder(string $out_trade_no, string $time_expire, float $money, string $openid, string $description = '', array $attach = []) : array
    {
        $response = $this->client()->postJson('v3/pay/transactions/jsapi', [
            'mchid'        => $this->config['mch_id'],
            'out_trade_no' => $out_trade_no,
            'time_expire'  => $time_expire,
            'appid'        => $this->config['app_id'],
            'description'  => $description,
            'notify_url'   => $this->config['notify_url'],
            'amount'       => [
                'total' => (int)($money * 100),
                'currency' => 'CNY'
            ],
            'payer' => [
                'openid' => $openid
            ],
            'attach' => json_encode($attach)
        ])->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['message']];
        }

        return [true, $response->toArray()];
    }

    /**
     * 微信下单，并获取小程序支付所需数据
     *
     * @param string $out_trade_no 订单号
     * @param float $money 金额
     * @param string $openid openid
     * @param string $time_expire 过期时间
     * @param string $description 商品描述
     * @param array $attach 附加数据，在查询API和支付通知中原样返回
     * @return array
     */
    public function appletsPlaceOrder(string $out_trade_no, float $money, string $openid, string $time_expire, string $description = '', array $attach = []) : array
    {
        [$is_error, $order_info] = $this->jsapiOrder($out_trade_no, $time_expire, $money, $openid, $description, $attach);

        if ($is_error) {
            return [true, $order_info['prepay_id'], $this->getPrepayIdToPayInfo($order_info['prepay_id'])];
        }

        return [false, '', []];
    }

    /**
     * 根据prepay_id获取支付所需数据
     *
     * @param string $prepay_id
     * @return array
     */
    public function getPrepayIdToPayInfo(string $prepay_id) : array
    {
        return $this->utils()->buildBridgeConfig($prepay_id, $this->config['app_id']);
    }

    /**
     * 订单退款
     *
     * @param string $out_trade_no
     * @param string $out_refund_no
     * @param string $reason
     * @param float $refund
     * @param float $total
     * @return array
     */
    public function orderRefund(string $out_trade_no, string $out_refund_no, string $reason, float $refund, float $total) : array
    {
        $response = $this->client()->postJson('v3/refund/domestic/refunds', [
            'out_trade_no'  => $out_trade_no,
            'out_refund_no' => $out_refund_no,
            'reason'        => $reason,
            'notify_url'    => $this->config['notify_url'],
            'amount'        => [
                'refund'   => (int)($refund * 100),
                'total'    => (int)($total * 100),
                'currency' => 'CNY'
            ],
        ])->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['message']];
        }

        return [true, $response->toArray()];
    }

    /**
     * 查询订单信息
     *
     * @param string $out_trade_no 商户订单号
     * @return array
     */
    public function getOrder(string $out_trade_no) : array
    {
        $response = $this->client()->get('v3/pay/transactions/out-trade-no/' . $out_trade_no, [
            'mchid' => $this->config['mch_id']
        ])->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['message']];
        }

        return [true, $response->toArray()];
    }

    /**
     * 查询退款订单信息
     *
     * @param string $out_refund_no
     * @return array
     */
    public function getRefundOrder(string $out_refund_no) : array
    {
        $response = $this->client()->get('v3/refund/domestic/refunds/' . $out_refund_no)->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['status']];
        }

        return [true, $response->toArray()];
    }

    /**
     * 商户转账至银行卡
     *
     * @param string $partner_trade_no 商户付款单号
     * @param string $enc_bank_no 收款方银行卡号
     * @param string $enc_true_name 收款方用户名
     * @param string $bank_code 收款方开户行
     * @param integer $amount 付款金额
     * @return array
     */
    public function payBank(string $partner_trade_no, string $enc_bank_no, string $enc_true_name, string $bank_code, int $amount) : array
    {
        $money = $amount * 100;

        $response = $this->client()->post('mmpaysptrans/pay_bank', [
            'xml'        => [
                'mch_id'           => $this->config['mch_id'],
                'partner_trade_no' => $partner_trade_no,
                'enc_bank_no'      => $this->publicEncrypt($this->config['public_key_pkcs8'], $enc_bank_no),
                'enc_true_name'    => $this->publicEncrypt($this->config['public_key_pkcs8'], $enc_true_name),
                'bank_code'        => $bank_code,
                'amount'           => $money,
                'desc'             => '钱包提现至银行卡：' . $money . '元'
            ],
            'local_cert' => $this->config['certificate'],
            'local_pk'   => $this->config['private_key'],
        ])->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['status']];
        }

        return [true, $response->toArray()];
    }

    /**
     * 查询转账至银行卡
     *
     * @param string $partner_trade_no 商户付款单号
     * @return array
     */
    public function queryBank(string $partner_trade_no) : array
    {
        $response = $this->client()->post('mmpaysptrans/query_bank', [
            'xml'        => [
                'mch_id'           => $this->config['mch_id'],
                'partner_trade_no' => $partner_trade_no,
                'sign_type'        => 'HMAC-SHA256'
            ],
            'local_cert' => $this->config['certificate'],
            'local_pk'   => $this->config['private_key'],
        ])->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['return_msg']];
        }

        return [true, $response->toArray()];
    }

    /**
     * 获取RSA加密公钥
     *
     * @return array
     */
    public function getPublicKey() : array
    {
        $response = $this->client()->post('https://fraud.mch.weixin.qq.com/risk/getpublickey', [
            'xml'        => [
                'mch_id'    => $this->config['mch_id'],
                'sign_type' => 'HMAC-SHA256'
            ],
            'local_cert' => $this->config['certificate'],
            'local_pk'   => $this->config['private_key'],
        ])->throw(false);

        if ($response->isFailed()) {
            return [false, $response->toArray()['return_msg']];
        }

        return [true, $response->toArray()];
    }

    /**
     * 公钥加密
     *
     * @param string $public_key_pkcs8
     * @param string $str
     * @return bool|string
     */
    protected function publicEncrypt(string $public_key_pkcs8, string $str)
    {
        if (openssl_public_encrypt($str, $encrypt_data, file_get_contents($public_key_pkcs8), OPENSSL_PKCS1_OAEP_PADDING)) { //加密成功，返回base64编码的字符串
            return base64_encode($encrypt_data);
        } else {
            return false;
        }
    }

    /**
     * 获取已下载的平台证书列表
     *
     * @param array $config
     * @return array
     */
    protected function getPlatformCertsList(array $config)
    {
        if(!empty($config['platform_certs_directory'])){
            if(!isset($this->platform_certs[$config['mch_id']])){
                $this->platform_certs[$config['mch_id']] = app(FileUtil::class)->getCertsList(app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . trim(str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', $this->config['platform_certs_directory'])), DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
            }
            return $this->platform_certs[$config['mch_id']];
        }

        return [];
    }

    /**
     * 更新下载平台证书
     *
     * @return void
     */
    public function updatePlatformCerts()
    {
        if(!empty($this->config['platform_certs_directory'])){
            [$status, $list] = $this->getPlatformCerts();

            if($status){
                
                $path = 'public' . DIRECTORY_SEPARATOR . trim(str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', $this->config['platform_certs_directory'])), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    
                app(FileUtil::class)->rmdir(app()->getRootPath() . $path);
    
                foreach ($list['data'] as $value) {
                    file_put_contents($path . $value['serial_no'] . '.pem', \sodium_crypto_aead_aes256gcm_decrypt(\base64_decode($value['encrypt_certificate']['ciphertext']), $value['encrypt_certificate']['associated_data'], $value['encrypt_certificate']['nonce'], $this->config['secret_key']));
                }
            }
        }
    }
}