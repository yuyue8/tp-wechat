<?php
declare (strict_types = 1);

namespace Yuyue8\TpWechat\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * Class WechatNoticeRecordModel
 * @package Yuyue8\TpWechat\model
 */
class WechatNoticeRecordModel extends Model
{
    use SoftDelete;
    protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = null;
    protected $pk                = 'id';
    protected $name              = 'wechat_notice_record';
    
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'msgid'         => 'char',
        'user_id'       => 'int',
        'user_type'     => 'int',
        'openid'        => 'char',
        'mode'          => 'varchar',
        'type'          => 'tinyint',
        'status'        => 'tinyint',
        'content'       => 'json',
        'error_message' => 'varchar',
        'receive_time'  => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'delete_time'   => 'int',
    ];
}