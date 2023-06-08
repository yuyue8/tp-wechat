<?php
namespace Yuyue8\TpWechat\services;

use Yuyue8\TpWechat\jobs\WechatNoticeSendJobs;
use Yuyue8\TpWechat\model\WechatNoticeRecordModel;
use Yuyue8\TpWechat\template\BaseTemplate;
use Yuyue8\TpWechat\template\Template;

/**
 * Class WechatNoticeRecordServices
 * @package Yuyue8\TpWechat\services
 */
class WechatNoticeRecordServices
{
    /**
     * 获取数据
     *
     * @param array $where
     * @param string $field
     * @return \think\model|null
     */
    public function getInfo(array $where, $field='*')
    {
        /** @var WechatNoticeRecordModel $wechatNoticeRecordModel */
        $wechatNoticeRecordModel = app(WechatNoticeRecordModel::class);

        return $wechatNoticeRecordModel->field($field)->where($where)->find();
    }

    /**
     * 修改数据
     *
     * @param array $where
     * @param array $data
     * @return mixed
     */
    public function updateInfo(array $where, array $data)
    {
        /** @var WechatNoticeRecordModel $wechatNoticeRecordModel */
        $wechatNoticeRecordModel = app(WechatNoticeRecordModel::class);

        return $wechatNoticeRecordModel->update($data, $where);
    }

    /**
     * 创建微信消息通知数据，并放入消息队列
     *
     * @param integer $user_id 用户ID
     * @param integer $user_type 用户类型
     * @param integer $openid 
     * @param string $tempCode 
     * @param array $data
     * @param integer $secs
     * @param string|null $link
     * @param string|null $appid
     * @param string|null $color
     * @return void
     */
    public function sendTemplate(int $user_id, int $user_type, int $openid, string $tempCode, array $data, int $secs = 0, string $link = null, string $appid = null, string $color = null)
    {
        if (empty($openid)) {
            return true;
        }

        /** @var BaseTemplate $template */
        $template = new Template('wechat');

        /** @var WechatNoticeRecordModel $wechatNoticeRecordModel */
        $wechatNoticeRecordModel = app(WechatNoticeRecordModel::class);
        $info = $wechatNoticeRecordModel->create([
            'user_id'   => $user_id,
            'user_type' => $user_type,
            'openid'    => $openid,
            'mode'      => 'wechat',
            'type'      => $tempCode,
            'status'    => 4,
            'content'   => $template->getNoticeData($openid, $tempCode, $data, $link, $appid, $color)
        ]);

        /** @var WechatNoticeSendJobs $wechatNoticeSendJobs */
        $wechatNoticeSendJobs = app(WechatNoticeSendJobs::class);
        if ($secs == 0) {
            $wechatNoticeSendJobs->dispatchDo('sendTemplate', [$info->id]);
        } else {
            $wechatNoticeSendJobs->dispatchDo('sendTemplate', [$info->id], $secs);
        }
        return true;
    }
}