<?php

namespace Yuyue8\TpWechat\template;

use Yuyue8\TpDriver\basic\BaseStorage;
use think\facade\Config;

abstract class BaseTemplate extends BaseStorage
{
    /**
     * 配置
     * @var array
     */
    protected $config = [];

    /**
     * 模板id
     * @var array
     */
    protected $templateIds = [];

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        $this->config = Config::get($this->configFile . '.stores.' . $this->name , []);
    }

    /**
     * 提取模板code
     * @param string $templateId
     * @return null
     */
    protected function getTemplateCode(string $templateId)
    {
        return $this->$this->config['template_id'][$templateId] ?? null;
    }

    /**
     * 发送消息
     * @param array $data
     * @return mixed
     */
    abstract public function send(array $data = []);

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
    abstract public function getNoticeData(string $openid, string $tempCode, array $data, string $link = null, ?string $appid = null, string $color = null);
}
