SendCloud Yii2 integration
=========================

This extension allow the developper to use [SendCloud](https://sendcloud.net/) as an email transport.
This work is inspired by [yii2-sendgrid](https://github.com/pgaultier/yii2-sendgrid).

Installation
------------

If you use Composer, you can update your composer.json like this :

``` json
{
    "require": {
        "softbread/yii2-sendcloud": "@dev"
    }
}
```

How to use it
------------

Add extension to your configuration

``` php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'SendCloud\Mail\Mailer',
            'user' => '<your sendcloud api-user>',
            'key'  => '<your sendcloud api-key>'
        ],
    ],
];
```

You can send email via SendCloud as mailer component 

``` php
Yii::$app->mailer->compose('contact/html')
     ->setFrom('from@domain.com')
     ->setTo($form->email)
     ->setSubject($form->subject)
     ->send();
```

For further instructions refer to the [related section in the Yii Definitive Guide](http://www.yiiframework.com/doc-2.0/guide-tutorial-mailing.html)
