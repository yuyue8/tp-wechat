<?php
declare (strict_types = 1);

namespace Yuyue8\TpWechat\model;

use think\Model;

/**
 * Class WechatUserModel
 * @package Yuyue8\TpWechat\model
 */
class WechatUserModel extends Model
{
    protected $pk                = 'id';
    protected $name              = 'wechat_user';
    
    // 设置字段信息
    protected $schema = [
        'id'        => 'int',
        'openid'    => 'char',
        'unionid'   => 'char',
        'subscribe' => 'int'
    ];
}