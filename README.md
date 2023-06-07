# TpWechat

## 安装
~~~
composer require yuyue8/tp-wechat
~~~

### 微信公众号

#### 微信公众号配置

在`tp_wechat`配置文件内修改`wechat`配置

#### 微信公众号服务接口

在控制器中添加以下代码，并在公众号后台设置该接口，则后续所有操作将自动完成
```
public function server(Yuyue8\TpWechat\interfaces\WechatInterface $wechat)
{
    ob_clean();
    
    return $wechat->server();
}
```

#### 微信公众号相关功能实现

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

订单支付通知和退款通知后续操作将会自动执行，默认没有任何后续操作，如需进行后续操作，进行一下操作即可

#### 微信支付相关功能实现

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
