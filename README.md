# TpWechat

## 安装
~~~
composer require yuyue8/tp-wechat
~~~

### 微信公众号

#### 微信公众号配置

在`tp_wechat`配置文件内修改`wechat`配置

#### 微信公众号服务接口

在控制器中添加以下代码，并在公众号后台设置该接口
```
public function server(Yuyue8\TpWechat\interfaces\WechatInterface $wechat)
{
    ob_clean();
    
    return $wechat->server();
}
```

#### 微信公众号内置功能

若使用内置的`关注`、`取消关注`、`模版消息`功能，需要创建相关数据表，创建语句如下：

```
CREATE TABLE `wechat_notice_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msgid` char(19) NOT NULL DEFAULT '' COMMENT '微信端消息记录ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '收取人ID',
  `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '收取人类型',
  `openid` char(28) NOT NULL DEFAULT '',
  `mode` varchar(20) NOT NULL DEFAULT '' COMMENT '通知模式 wechat',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '通知类型',
  `status` tinyint(1) NOT NULL DEFAULT '4' COMMENT '发送状态 1-发送中 2-成功 3-失败 4-待发送 5-取消',
  `content` json NOT NULL COMMENT '消息内容',
  `error_message` varchar(10) NOT NULL DEFAULT '' COMMENT '错误消息',
  `receive_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回执时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  `delete_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `msgid` (`msgid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信公众号-消息通知记录';

CREATE TABLE `wechat_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增列',
  `openid` char(28) NOT NULL DEFAULT '' COMMENT 'openid',
  `unionid` char(28) NOT NULL DEFAULT '' COMMENT '公众号绑定开放平台后，用户信息会有此字段信息',
  `subscribe` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1已关注，2取消关注',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_openid` (`openid`,`subscribe`) USING BTREE,
  KEY `idx_unionid` (`unionid`,`subscribe`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COMMENT='微信公众号-用户信息';
```

然后运行`Yuyue8\TpWechat\jobs\WechatNoticeSendJobs`消息队列，对消息进行处理

###### 模版消息发送

在`template`配置文件设置模版消息相关配置，然后调用`Yuyue8\TpWechat\services\WechatNoticeRecordServices`类的`sendTemplate`方法，

将会保存消息记录到数据表，并进入`Yuyue8\TpWechat\jobs\WechatNoticeSendJobs`消息队列

#### 自定义微信公众号相关功能

如需自定义微信公众号相关功能，只需以下步骤：

创建微信服务类，继承`Yuyue8\TpWechat\service\WechatService`类或者`Yuyue8\TpWechat\interfaces\WechatInterface`接口，并实现相关方法
在`provider.php`文件中绑定微信服务类到接口

例如：
创建的微信服务类为`app\service\Wechat`,在`provider.php`文件中添加
```
Yuyue8\TpWechat\interfaces\WechatInterface::class => app\service\Wechat::class,
```

后续所有操作将由`app\service\Wechat`类完成

### 微信支付

#### 微信支付配置

在`tp_wechat`配置文件内修改`wechat_pay`配置

#### 微信支付服务接口

在控制器中添加以下代码，并在商户号后台设置该接口，以接受订单支付通知和退款通知
```
public function server(Yuyue8\TpWechat\interfaces\WechatPayInterface $pay)
{
    ob_clean();
    
    return $pay->server();
}
```

由于订单数据表没有规则，所以请自行实现订单支付通知和退款通知后续操作，具体步骤如下：

🚨🚨🚨 注意：推送信息不一定靠谱哈，请务必验证
建议是拿订单号调用微信支付查询接口，以查询到的订单状态为准

#### 自定义微信支付相关功能

`Yuyue8\TpWechat\service\WechatPayService`类内置了下单退单等功能，直接调用即可

如需自定义支付相关功能，只需以下步骤：

创建微信支付服务类，继承`Yuyue8\TpWechat\service\WechatPayService`类或者`Yuyue8\TpWechat\interfaces\WechatPayInterface`接口，并实现相关方法
在`provider.php`文件中绑定微信支付服务类到接口

例如：
创建的微信支付服务类为`app\service\PayWechat`,在`provider.php`文件中添加
```
Yuyue8\TpWechat\interfaces\WechatPayInterface::class => app\service\PayWechat::class,
```

#### 更新微信支付平台证书

`Yuyue8\TpWechat\service\WechatPayService`类中的`updatePlatformCerts`方法可以更新微信支付平台证书，请每个商户号每天执行一次

## 警告

考虑到效率和多商户的情况，`Yuyue8\TpWechat\service\WechatService`类和`Yuyue8\TpWechat\service\WechatPayService`类中使用了单例且属性值为数组，所以在修改了`tp_wechat`配置文件或更新平台证书后，一定要`重启`或`重置`以下类，否则修改的配置和平台证书将不会生效

`Yuyue8\TpWechat\service\WechatService`类、`Yuyue8\TpWechat\service\WechatPayService`类和`Yuyue8\TpWechat\interfaces\WechatPayInterface`接口

例如：
```
app(WechatService::class, [], true);
app(WechatPayService::class, [], true);
app(WechatPayInterface::class, [], true);
```