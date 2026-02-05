<?php

namespace Phpais\AiPlugin\Hyperf\Provider;

use Hyperf\Di\Container;
use Hyperf\Contract\ConfigInterface;
use Hyperf\ServiceProvider\AbstractServiceProvider;
use Phpais\AiPlugin\Factories\AiClientFactory;

class AiPluginServiceProvider extends AbstractServiceProvider
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function register()
    {
        // 注册AI客户端实例到容器
        $this->container->set('ai', function () {
            $config = $this->container->get(ConfigInterface::class)->get('ai', []);
            $defaultProvider = $config['default'] ?? 'wenxin';
            $providerConfig = $config['providers'][$defaultProvider] ?? [];

            return AiClientFactory::create($defaultProvider, $providerConfig);
        });

        // 注册AI工厂到容器
        $this->container->set('ai.factory', AiClientFactory::class);
    }

    public function boot()
    {
        // 可以在这里进行其他初始化操作
    }
}