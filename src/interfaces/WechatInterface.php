<?php

namespace Yuyue8\TpWechat\interfaces;

use Psr\Http\Message\ResponseInterface;

interface WechatInterface
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
     * 获取用户信息
     *
     * @param string $openid
     * @return array
     */
    public function getUserInfo(string $openid) : array;

    /**
     * 发送模版消息
     *
     * @param array $content
     * @return array
     */
    public function sendTemplateMessage(array $content) : array;
}
