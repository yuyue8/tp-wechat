<?php
namespace Yuyue8\TpWechat\template\storage;

use Yuyue8\TpWechat\template\BaseTemplate;

/**
 * 订阅消息
 * Class Subscribe
 * @package crmeb\services\template\storage
 */
class Subscribe extends BaseTemplate
{

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        parent::initialize($config); // TODO: Change the autogenerated stub
    }

    /**
     * 发送消息
     * @param string $templateId
     * @param array $data
     * @return bool|mixed
     */
    public function send(array $data = [])
    {
        
    }

    /**
     * 获取消息数据
     *
     * @param string $openid
     * @param string $tempCode
     * @param array $data
     * @param string|null $link
     * @param string|null $appid
     * @param string|null $color
     * @return array
     */
    public function getNoticeData(string $openid, string $tempCode, array $data, ?string $link = null, ?string $appid = null, ?string $color = null)
    {
        
    }
}