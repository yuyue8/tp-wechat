<?php

namespace Yuyue8\TpWechat;

class Service extends \think\Service
{

    /**
     * 服务启动
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind([
            \Yuyue8\TpWechat\interfaces\WechatInterface::class      => \Yuyue8\TpWechat\service\WechatService::class,
            \Yuyue8\TpWechat\interfaces\WechatPayInterface::class   => \Yuyue8\TpWechat\service\WechatPayService::class
        ]);
    }

}
