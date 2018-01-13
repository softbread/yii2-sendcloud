SendCloud服务的Yii2组件
=========================
[English Guide](README.md)

这是一个Yii2框架的组件，该组件可以替代Yii2的SwiftMailer实现发送邮件。
利用[SendCloud](https://sendcloud.net/)的API，用户可以在SMTP端口被封的情况下发送邮件，
也可以利用SendCloud的模版和联系人列表实现更更复杂的邮件推送功能。
这个组件是看到[yii2-sendgrid](https://github.com/pgaultier/yii2-sendgrid)而收到的启发。

安装
------------

添加 ``yii-sendcloud`` 到你的composer配置文件 composer.json :

``` json
{
    "require": {
        "softbread/yii2-sendcloud": "@dev"
    }
}
```

如何使用
------------

先把这个组件添加到web.conf的mailer:

``` php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'SendCloud\Mail\Mailer',
            'api_user' => '<your sendcloud api-user>',
            'api_key'  => '<your sendcloud api-key>'
        ],
    ],
];
```

你就可以用Yii2的发邮件功能了实现真正的邮件功能了：

``` php
Yii::$app->mailer->compose('contact/html')
     ->setFrom('from@domain.com')
     ->setTo($form->email)
     ->setSubject($form->subject)
     ->send();
```

Yii2 Mailer更详细的用法参见：[Yii2文档发送邮件部分](http://www.yiiframework.com/doc-2.0/guide-tutorial-mailing.html)

当然，这个扩展也支持SendCloud的模版生成的邮件：

``` php
Yii::$app->mailer->compose('contact/html')
     ->setFrom('from@domain.com')
     ->setTo([email1, email2])
     ->setSubject($form->subject)
     ->setTemplateName('revoke-name')
     ->setTemplateVars([
            '%name%' => ['X1', 'X2'],
            '%money% => [12.5, 1.9]
        ])
     ->send();
```
