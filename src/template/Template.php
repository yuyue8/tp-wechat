<?php
namespace Yuyue8\TpWechat\template;

use Yuyue8\TpDriver\basic\BaseManager;
use think\facade\Config;

/**
 * Class Template
 * @package data\service\template
 */
class Template extends BaseManager
{

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\Yuyue8\\TpWechat\\template\\storage\\';

    /**
     * 设置默认
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('tp_template.default', '');
    }
}
