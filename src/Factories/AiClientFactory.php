<?php

namespace Phpais\AiPlugin\Factories;

use Phpais\AiPlugin\Contracts\AiClientInterface;

class AiClientFactory
{
    /**
     * 创建AI客户端实例
     * 
     * @param string $provider 模型提供商
     * @param array $config 配置参数
     * @return AiClientInterface AI客户端实例
     * @throws \Exception 当模型提供商不支持时抛出异常
     */
    public static function create(string $provider, array $config): AiClientInterface
    {
        $provider = strtolower($provider);
        $className = 'Phpais\\AiPlugin\\Clients\\' . ucfirst($provider) . 'Client';
        
        if (!class_exists($className)) {
            throw new \Exception('Unsupported AI provider: ' . $provider);
        }
        
        return new $className($config);
    }
    
    /**
     * 获取支持的模型提供商列表
     * 
     * @return array 支持的模型提供商列表
     */
    public static function getSupportedProviders(): array
    {
        return [
            'wenxin',    // 文心
            'qianwen',   // 千问
            'volcano',   // 火山
            'deepseek',  // DeepSeek
            'hunyuan',   // 混元大模型
            'zhipu',     // 智谱清言
            'moonshot',  // 月之暗面
        ];
    }
}
