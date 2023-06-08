<?php

namespace Yuyue8\TpWechat\jobs;

use Yuyue8\TpQueue\basic\BaseJobs;
use Yuyue8\TpQueue\traits\QueueTrait;
use Yuyue8\TpWechat\service\WechatService;
use Yuyue8\TpWechat\services\WechatNoticeRecordServices;
use Yuyue8\TpWechat\services\WechatUserServices;
use Yuyue8\TpWechat\template\BaseTemplate;
use Yuyue8\TpWechat\template\Template;

/**
 * Class WechatNoticeSendJobJobs
 * @package data\jobs\wechat
 */
class WechatNoticeSendJobs extends BaseJobs
{
    use QueueTrait;

    protected $queueName = 'WechatNoticeSendJobJobs';

    /**
     * 发送模版消息
     */
    public function sendTemplate($id)
    {
        /** @var WechatNoticeRecordServices $wechatNoticeRecordServices */
        $wechatNoticeRecordServices = app(WechatNoticeRecordServices::class);
        $info = $wechatNoticeRecordServices->getInfo([
            ['id', '=', $id]
        ]);

        if(!$info || $info['status'] != 4){
            return true;
        }

        /** @var BaseTemplate $template */
        $template = new Template($info['mode']);
        $data = $template->send($info['content']);
        
        switch ($info['mode']) {
            case 'wechat':
                if(isset($data['errmsg']) && $data['errmsg'] == 'ok'){
                    $wechatNoticeRecordServices->updateInfo([
                        ['id', '=', $id]
                    ], [
                        'status' => 1,
                        'msgid'  => $data['msgid']
                    ]);
                }else{
                    $wechatNoticeRecordServices->updateInfo([
                        ['id', '=', $id]
                    ], [
                        'msgid'         => $data['msgid'] ?? '',
                        'error_message' => $data['errmsg'] ?? ''
                    ]);
                }
                break;
            
            default:
                # code...
                break;
        }

        return true;
    }

    /**
     * 消息回执处理
     */
    public function noticeFinish($MsgID,$status)
    {
        /** @var WechatNoticeRecordServices $wechatNoticeRecordServices */
        $wechatNoticeRecordServices = app(WechatNoticeRecordServices::class);
        if(!($info = $wechatNoticeRecordServices->getInfo([['msgid','=',$MsgID]], 'id,status')) || $info['status'] != 1){
            return true;
        }

        $wechatNoticeRecordServices->updateInfo([
            ['id', '=', $info['id']]
        ],[
            'receive_time'  => time(),
            'status'        => $status == 'success' ? 2 : 3,
            'error_message' => $status == 'failed:user block' ? '用户拒收' : ($status == 'success' ? '' : '系统错误')
        ]);

        return true;
    }

    /**
     * 关注公众号
     *
     * @param string $openid
     * @return bool
     */
    public function subscribe(string $openid)
    {
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app(WechatUserServices::class);

        if($info = $wechatUserServices->getInfo([['openid', '=', $openid]])){
            $data = [
                'subscribe' => 1
            ];
            if($info['unionid'] == ''){
                /** @var WechatService $wechatService */
                $wechatService = app(WechatService::class);
                $user_info = $wechatService->getUserInfo($openid);
                $data['unionid'] = $user_info['unionid'];
            }

            $wechatUserServices->updateInfo([
                ['id', '=', $info['id']]
            ], $data);
        }else{
            /** @var WechatService $wechatService */
            $wechatService = app(WechatService::class);
            $user_info = $wechatService->getUserInfo($openid);

            $wechatUserServices->createInfo([
                'openid'    => $openid,
                'unionid'   => $user_info['unionid'],
                'subscribe' => 1
            ]);
        }

        return true;
    }

    /**
     * 取消关注
     *
     * @param string $openid
     * @return bool
     */
    public function unsubscribe(string $openid)
    {
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app(WechatUserServices::class);

        $wechatUserServices->updateInfo([
            ['openid', '=', $openid]
        ],[
            'subscribe' => 2
        ]);
        
        return true;
    }
}
