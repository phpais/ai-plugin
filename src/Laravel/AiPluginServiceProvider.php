<?php

namespace Phpais\AiPlugin\Laravel;

use Illuminate\Support\ServiceProvider;
use Phpais\AiPlugin\Factories\AiClientFactory;

class AiPluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 合并配置文件
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/ai.php', 'ai'
        );
        
        // 绑定AI客户端实例到容器
        $this->app->singleton('ai', function ($app) {
            $config = $app['config']->get('ai', []);
            $defaultProvider = $config['default'] ?? 'wenxin';
            $providerConfig = $config['providers'][$defaultProvider] ?? [];
            
            return AiClientFactory::create($defaultProvider, $providerConfig);
        });
        
        // 绑定AI工厂到容器
        $this->app->bind('ai.factory', AiClientFactory::class);
    }
    
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__ . '/../Config/ai.php' => config_path('ai.php'),
        ], 'config');
    }
}
