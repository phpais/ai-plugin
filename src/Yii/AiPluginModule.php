<?php

namespace Phpais\AiPlugin\Yii;

use yii\base\Module;
use Phpais\AiPlugin\Factories\AiClientFactory;

class AiPluginModule extends Module
{
    public $controllerNamespace = 'Phpais\AiPlugin\Yii\controllers';

    public function init()
    {
        parent::init();

        // 注册AI服务到Yii容器
        \Yii::$container->set('ai', function () {
            $config = \Yii::$app->params['ai'] ?? require __DIR__ . '/../Config/ai.php';
            $defaultProvider = $config['default'] ?? 'wenxin';
            $providerConfig = $config['providers'][$defaultProvider] ?? [];

            return AiClientFactory::create($defaultProvider, $providerConfig);
        });

        // 注册AI工厂到Yii容器
        \Yii::$container->set('ai.factory', AiClientFactory::class);
    }
}