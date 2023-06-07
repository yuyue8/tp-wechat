<?php

namespace Yuyue8\TpWechat\interfaces;

use Psr\Http\Message\ResponseInterface;

interface WechatPayInterface
{

    /**
     * 设置配置
     *
     * @param array $config
     * @return array
     */
    public function setConfig(array $config) : array;

    public function server() : ResponseInterface;

    /**
     * jsapi下单
     *
     * @param string $out_trade_no 订单号
     * @param string $time_expire 过期时间
     * @param float $money 金额
     * @param string $openid openid
     * @param string $description 商品描述
     * @param array $attach 附加数据，在查询API和支付通知中原样返回
     * @return array [$is_error:false|true, $content]
     */
    public function jsapiOrder(string $out_trade_no, string $time_expire, float $money, string $openid, string $description = '', array $attach = []) : array;

    /**
     * 微信下单，并获取小程序支付所需数据
     *
     * @param string $out_trade_no 订单号
     * @param float $money 金额
     * @param string $openid openid
     * @param string $time_expire 过期时间
     * @param string $description 商品描述
     * @param array $attach 附加数据，在查询API和支付通知中原样返回
     * @return array [$is_error:false|true, $prepay_id, $content]
     */
    public function appletsPlaceOrder(string $out_trade_no, float $money, string $openid, string $time_expire, string $description = '', array $attach = []) : array;

    /**
     * 根据prepay_id获取支付所需数据
     *
     * @param string $prepay_id
     * @return array
     */
    public function getPrepayIdToPayInfo(string $prepay_id) : array;

    /**
     * 订单退款
     *
     * @param string $out_trade_no
     * @param string $out_refund_no
     * @param string $reason
     * @param float $refund
     * @param float $total
     * @return array [$is_error:false|true, $content]
     */
    public function orderRefund(string $out_trade_no, string $out_refund_no, string $reason, float $refund, float $total) : array;

    /**
     * 查询订单信息
     *
     * @param string $out_trade_no 商户订单号
     * @return array [$is_error:false|true, $content]
     */
    public function getOrder(string $out_trade_no) : array;

    /**
     * 查询退款订单信息
     *
     * @param string $out_refund_no
     * @return array [$is_error:false|true, $content]
     */
    public function getRefundOrder(string $out_refund_no) : array;

    /**
     * 商户转账至银行卡
     *
     * @param string $partner_trade_no 商户付款单号
     * @param string $enc_bank_no 收款方银行卡号
     * @param string $enc_true_name 收款方用户名
     * @param string $bank_code 收款方开户行
     * @param integer $amount 付款金额
     * @return array [$is_error:false|true, $content]
     */
    public function payBank(string $partner_trade_no, string $enc_bank_no, string $enc_true_name, string $bank_code, int $amount) : array;

    /**
     * 查询转账至银行卡
     *
     * @param string $partner_trade_no 商户付款单号
     * @return array [$is_error:false|true, $content]
     */
    public function queryBank(string $partner_trade_no) : array;

    /**
     * 获取RSA加密公钥
     *
     * @return array [$is_error:false|true, $content]
     */
    public function getPublicKey() : array;
}
