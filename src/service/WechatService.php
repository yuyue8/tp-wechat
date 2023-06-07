<?php

namespace Yuyue8\TpWechat\service;

use EasyWeChat\OfficialAccount\Application;
use EasyWeChat\OfficialAccount\Message;
use EasyWeChat\OfficialAccount\Server;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use think\facade\Config;
use Yuyue8\TpWechat\interfaces\WechatInterface;

/**微信公众号
 * Class WechatService
 * @package service
 */
abstract class WechatService implements WechatInterface
{
    /**
     * @var Application
     */
    protected $instance;

    protected $client;

    protected $server;

    protected $config;

    public function __construct()
    {
        $this->config = Config::get('tp_wechat.wechat');
    }

    /**
     * 设置配置
     *
     * @param array $config
     * @return array
     */
    public function setConfig(array $config) : array
    {
        $this->config = $config;

        return $config;
    }
    
    /**
     * 初始化
     * 
     * @return Application
     */
    public function application()
    {
        !isset($this->instance[$this->config['app_id']]) && ($this->instance[$this->config['app_id']] = new Application($this->config));
        return $this->instance[$this->config['app_id']];
    }

    public function client()
    {
        !isset($this->client[$this->config['app_id']]) && ($this->client[$this->config['app_id']] = $this->application()->getClient());
        return $this->client[$this->config['app_id']];
    }

    /**
     * 公众号服务
     *
     */
    public function server() : ResponseInterface
    {
        $request = request();
        $symfony_request = new HttpFoundationRequest($request->get(), $request->post(), [], $request->cookie(), [], [], $request->getContent());
        $symfony_request->headers = new HeaderBag($request->header());

        /** @var Application $wechat */
        $wechat = $this->application();
        $wechat->setRequestFromSymfonyRequest($symfony_request);
        $server = $wechat->getServer();

        return $this->hook($server)->serve();
    }

    /**
     * 监听行为
     *
     * @param Server $server
     * @return Server
     */
    private function hook(Server $server)
    {
        $server->addMessageListener('text',function($message, \Closure $next){
            return $this->textMessage($message);
        });

        //关注事件
        $server->addEventListener('subscribe',function($message, \Closure $next){
            return $this->subscribeEvent($message);
        });

        //取消关注事件
        $server->addEventListener('unsubscribe',function($message, \Closure $next){

            $this->unSubscribeEvent($message);

            return $next($message);
        });

        //模板消息回执
        $server->addEventListener('TEMPLATESENDJOBFINISH',function($message, \Closure $next){

            $this->templateMessageNotice($message);

            return $next($message);
        });

        return $server;
    }

    /**
     * 文字消息
     *
     * @param Message $message Content：消息内容
     * @return string
     */
    protected function textMessage(Message $message) : string
    {
        return $message->Content;
    }

    /**
     * 关注事件
     *
     * @param Message $message FromUserName：发送方OpenID
     * @return string
     */
    protected function subscribeEvent(Message $message) : string
    {
        return '感谢您的关注！';
    }

    /**
     * 取消关注事件
     *
     * @param Message $message FromUserName：发送方OpenID
     * @return void
     */
    protected function unSubscribeEvent(Message $message)
    {

    }

    /**
     * 模版消息通知
     *
     * @param Message $message MsgID：消息ID，Status：消息状态
     * @return void
     */
    protected function templateMessageNotice(Message $message)
    {

    }

    /**
     * 获取用户信息
     *
     * @param string $code
     * @return array
     */
    public function getUserInfo(string $openid) : array
    {
        return json_decode($this->client()->get('cgi-bin/user/info',[
            'openid' => $openid,
            'lang'   => 'zh_CN'
        ])->getContent(),true);
    }

    /**
     * 发送模版消息
     *
     * @param array $data
     * @return array
     */
    public function sendTemplateMessage(array $data) : array
    {
        return json_decode($this->client()->postJson('cgi-bin/message/template/send',$data)->getContent(),true);
    }
}