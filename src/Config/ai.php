<?php

return [
    // 默认AI模型提供者
    'default' => env('AI_DEFAULT', 'wenxin'),
    
    // AI模型提供者配置
    'providers' => [
        // 文心AI配置
        'wenxin' => [
            'api_key' => env('AI_WENXIN_API_KEY', ''),
            'model' => env('AI_WENXIN_MODEL', 'ernie-bot'),
            'endpoint' => env('AI_WENXIN_ENDPOINT', 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions'),
            'timeout' => env('AI_WENXIN_TIMEOUT', 30),
            'provider' => 'wenxin',
        ],
        
        // 千问AI配置
        'qianwen' => [
            'api_key' => env('AI_QIANWEN_API_KEY', ''),
            'model' => env('AI_QIANWEN_MODEL', 'ep-20240101123456-abcde'),
            'endpoint' => env('AI_QIANWEN_ENDPOINT', 'https://ark.cn-beijing.volces.com/api/v3/chat/completions'),
            'timeout' => env('AI_QIANWEN_TIMEOUT', 30),
            'provider' => 'qianwen',
        ],
        
        // 火山AI配置
        'volcano' => [
            'api_key' => env('AI_VOLCANO_API_KEY', ''),
            'model' => env('AI_VOLCANO_MODEL', 'ep-20240101123456-abcde'),
            'endpoint' => env('AI_VOLCANO_ENDPOINT', 'https://ark.cn-beijing.volces.com/api/v3/chat/completions'),
            'timeout' => env('AI_VOLCANO_TIMEOUT', 30),
            'provider' => 'volcano',
        ],
        
        // DeepSeek AI配置
        'deepseek' => [
            'api_key' => env('AI_DEEPSEEK_API_KEY', ''),
            'model' => env('AI_DEEPSEEK_MODEL', 'deepseek-chat'),
            'endpoint' => env('AI_DEEPSEEK_ENDPOINT', 'https://api.deepseek.com/v1/chat/completions'),
            'timeout' => env('AI_DEEPSEEK_TIMEOUT', 30),
            'provider' => 'deepseek',
        ],
        
        // 混元大模型配置
        'hunyuan' => [
            'api_key' => env('AI_HUNYUAN_API_KEY', ''),
            'model' => env('AI_HUNYUAN_MODEL', 'hunyuan-pro'),
            'endpoint' => env('AI_HUNYUAN_ENDPOINT', 'https://api.hunyuan.cn/v1/chat/completions'),
            'timeout' => env('AI_HUNYUAN_TIMEOUT', 30),
            'provider' => 'hunyuan',
        ],
        
        // 智谱清言配置
        'zhipu' => [
            'api_key' => env('AI_ZHIPU_API_KEY', ''),
            'model' => env('AI_ZHIPU_MODEL', 'glm-4'),
            'endpoint' => env('AI_ZHIPU_ENDPOINT', 'https://open.bigmodel.cn/api/mcp/v1/chat/completions'),
            'timeout' => env('AI_ZHIPU_TIMEOUT', 30),
            'provider' => 'zhipu',
        ],
        
        // 月之暗面配置
        'moonshot' => [
            'api_key' => env('AI_MOONSHOT_API_KEY', ''),
            'model' => env('AI_MOONSHOT_MODEL', 'moonshot-v1-8k'),
            'endpoint' => env('AI_MOONSHOT_ENDPOINT', 'https://api.moonshot.cn/v1/chat/completions'),
            'timeout' => env('AI_MOONSHOT_TIMEOUT', 30),
            'provider' => 'moonshot',
        ],
    ],
];
