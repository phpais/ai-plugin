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
            'endpoint' => env('AI_WENXIN_ENDPOINT', 'https://qianfan.baidubce.com/v2/chat/completions'),
            'timeout' => env('AI_WENXIN_TIMEOUT', 30),
            'provider' => 'wenxin',
        ],
        
        // 千问AI配置
        'qianwen' => [
            'api_key' => env('AI_QIANWEN_API_KEY', ''),
            'model' => env('AI_QIANWEN_MODEL', 'ep-20240101123456-abcde'),
            'endpoint' => env('AI_QIANWEN_ENDPOINT', 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions'),
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
            'endpoint' => env('AI_DEEPSEEK_ENDPOINT', 'https://api.deepseek.com/chat/completions'),
            'timeout' => env('AI_DEEPSEEK_TIMEOUT', 30),
            'provider' => 'deepseek',
        ],
        
        // 混元大模型配置
        'hunyuan' => [
            'api_key' => env('AI_HUNYUAN_API_KEY', ''),
            'model' => env('AI_HUNYUAN_MODEL', 'hunyuan-pro'),
            'endpoint' => env('AI_HUNYUAN_ENDPOINT', 'https://api.hunyuan.cloud.tencent.com/v1/chat/completions'),
            'timeout' => env('AI_HUNYUAN_TIMEOUT', 30),
            'provider' => 'hunyuan',
        ],
        
        // 智谱清言配置
        'zhipu' => [
            'api_key' => env('AI_ZHIPU_API_KEY', ''),
            'model' => env('AI_ZHIPU_MODEL', 'glm-4'),
            'endpoint' => env('AI_ZHIPU_ENDPOINT', 'https://open.bigmodel.cn/api/paas/v4/chat/completions'),
            'timeout' => env('AI_ZHIPU_TIMEOUT', 30),
            'provider' => 'zhipu',
        ],
        
        // Minmax配置
        'minmax' => [
            'api_key' => env('AI_MINMAX_API_KEY', ''),
            'model' => env('AI_MINMAX_MODEL', 'abab5.5-chat'),
            'endpoint' => env('AI_MINMAX_ENDPOINT', 'https://api.minimaxi.com/v1/text/chatcompletion_v2'),
            'timeout' => env('AI_MINMAX_TIMEOUT', 30),
            'provider' => 'minmax',
        ],
        
        // Kimi配置
        'kimi' => [
            'api_key' => env('AI_KIMI_API_KEY', ''),
            'model' => env('AI_KIMI_MODEL', 'kimi'),
            'endpoint' => env('AI_KIMI_ENDPOINT', 'https://api.moonshot.cn/v1/chat/completions'),
            'timeout' => env('AI_KIMI_TIMEOUT', 30),
            'provider' => 'kimi',
        ],
        
        // ChatGPT配置
        'chatgpt' => [
            'api_key' => env('AI_CHATGPT_API_KEY', ''),
            'model' => env('AI_CHATGPT_MODEL', 'gpt-3.5-turbo'),
            'endpoint' => env('AI_CHATGPT_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
            'timeout' => env('AI_CHATGPT_TIMEOUT', 30),
            'provider' => 'chatgpt',
        ],
        
        // Gemini配置
        'gemini' => [
            'api_key' => env('AI_GEMINI_API_KEY', ''),
            'model' => env('AI_GEMINI_MODEL', 'gemini-pro'),
            'endpoint' => env('AI_GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent'),
            'timeout' => env('AI_GEMINI_TIMEOUT', 30),
            'provider' => 'gemini',
        ],
    ],
];
