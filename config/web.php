<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
        'definitions' => [
            \app\interfaces\BookRepositoryInterface::class => \app\repositories\BookRepository::class,
            \app\interfaces\AuthorRepositoryInterface::class => \app\repositories\AuthorRepository::class,
            \app\interfaces\SubscriptionRepositoryInterface::class => \app\repositories\SubscriptionRepository::class,
            \app\services\SmsService::class => \app\services\SmsService::class,
            \app\services\BookService::class => \app\services\BookService::class,
            \app\services\AuthorService::class => \app\services\AuthorService::class,
            \app\services\SubscriptionService::class => \app\services\SubscriptionService::class,
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'SDfKY6Rmolu4P7tOGz2sRgSU-Jvb2whM',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'categories' => ['book', 'sms', 'yii\httpclient\*'],
                    'logVars' => ['_GET', '_POST'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'books' => 'book/index',
                'book/<id:\d+>' => 'book/view',
                'book/create' => 'book/create',
                'book/update/<id:\d+>' => 'book/update',
                'book/delete/<id:\d+>' => 'book/delete',

                'authors' => 'author/index',
                'author/<id:\d+>' => 'author/view',
                'author/create' => 'author/create',
                'author/update/<id:\d+>' => 'author/update',
                'author/delete/<id:\d+>' => 'author/delete',

                'subscribe' => 'site/subscribe',
                'top-authors' => 'site/top-authors',
                'top-authors/<year:\d{4}>' => 'site/top-authors',

                '' => 'book/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
}

return $config;
