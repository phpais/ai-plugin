<?php

namespace Phpais\AiPlugin\ThinkPHP;

use think\Service as BaseService;
use Phpais\AiPlugin\Factories\AiClientFactory;

class Service extends BaseService
{
    public function register()
    {
        // 绑定AI客户端实例到容器
        $this->app->bind('ai', function () {
            $config = $this->app->config->get('ai', []);
            $defaultProvider = $config['default'] ?? 'wenxin';
            $providerConfig = $config['providers'][$defaultProvider] ?? [];
            
            return AiClientFactory::create($defaultProvider, $providerConfig);
        });
        
        // 绑定AI工厂到容器
        $this->app->bind('ai.factory', AiClientFactory::class);
    }
    
    public function boot()
    {
        // 加载配置文件
        $this->loadConfig();
    }
    
    protected function loadConfig()
    {
        // 发布配置文件到应用目录
        $this->publishes([
            __DIR__ . '/../Config/ai.php' => $this->app->getConfigPath() . 'ai.php',
        ], 'config');
    }
}
