<?php

namespace Phpais\AiPlugin\Slim;

use Slim\App;
use Phpais\AiPlugin\Factories\AiClientFactory;

class AiPluginProvider
{
    /**
     * 注册AI服务到Slim应用
     *
     * @param App $app
     * @param array $config
     */
    public static function register(App $app, array $config = [])
    {
        // 如果没有提供配置，使用默认配置
        if (empty($config)) {
            $config = require __DIR__ . '/../Config/ai.php';
        }

        // 获取默认提供者
        $defaultProvider = $config['default'] ?? 'wenxin';
        $providerConfig = $config['providers'][$defaultProvider] ?? [];

        // 注册AI客户端到容器
        $app->getContainer()->set('ai', function () use ($defaultProvider, $providerConfig) {
            return AiClientFactory::create($defaultProvider, $providerConfig);
        });

        // 注册AI工厂到容器
        $app->getContainer()->set('ai.factory', function () {
            return new AiClientFactory();
        });

        // 注册配置到容器
        $app->getContainer()->set('ai.config', $config);
    }
}