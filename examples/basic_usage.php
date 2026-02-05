<?php

require __DIR__ . '/../vendor/autoload.php';

use Phpais\AiPlugin\Factories\AiClientFactory;

// 配置示例
$config = [
    'deepseek' => [
        'api_key' => 'your_deepseek_api_key',
        'model' => 'deepseek-chat',
        'endpoint' => 'https://api.deepseek.com/chat/completions',
        'timeout' => 30,
        'provider' => 'deepseek',
    ],
];

echo "PHP AI 插件 DeepSeek 对话调用示例\n";
echo "==============================\n\n";

// 1. 正常调用示例
echo "1. 正常调用示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    $result = $deepseekClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
    echo "使用情况：" . json_encode($result['usage']) . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 2. 带参数的自定义提交数据示例
echo "2. 带参数的自定义提交数据示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    
    // 自定义参数
    $options = [
        'temperature' => 0.8,  // 温度参数，控制输出的随机性
        'max_tokens' => 1024,  // 最大生成token数
        'system' => '你是一位专业的技术顾问，擅长解答编程问题',  // 系统提示词
        'top_p' => 0.9,  // 核采样参数
        'frequency_penalty' => 0.1,  // 频率惩罚
        'presence_penalty' => 0.1,  // 存在惩罚
    ];
    
    $result = $deepseekClient->chat('如何用PHP实现一个简单的RESTful API？', $options);
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 3. 多轮对话示例（自定义提交历史消息）
echo "3. 多轮对话示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    
    // 自定义历史消息
    $historyMessages = [
        ['role' => 'user', 'content' => '你好，我想学习PHP编程'],
        ['role' => 'assistant', 'content' => '你好！学习PHP是一个很好的选择。PHP是一种广泛使用的服务器端脚本语言，特别适合Web开发。你是完全的初学者，还是已经有一些编程经验了呢？'],
        ['role' => 'user', 'content' => '我是初学者，应该从哪里开始学习？']
    ];
    
    $options = [
        'messages' => $historyMessages,
        'temperature' => 0.7,
    ];
    
    $result = $deepseekClient->chat('我是初学者，应该从哪里开始学习？', $options);
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 4. 自定义系统提示词示例
echo "4. 自定义系统提示词示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    
    $options = [
        'system' => '你是一位专业的厨师，擅长制作各种美食。请用简洁明了的语言回答关于烹饪的问题，提供详细的步骤和技巧。',
        'temperature' => 0.8,
    ];
    
    $result = $deepseekClient->chat('如何制作意大利面？', $options);
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 5. 流式响应示例
echo "5. 流式响应示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    
    echo "流式响应：";
    ob_flush();
    flush();
    
    $deepseekClient->streamChat('写一段关于人工智能发展的简短段落', function ($chunk) {
        echo $chunk;
        ob_flush();
        flush();
        usleep(100000); // 暂停100ms，模拟流式效果
    });
    
echo "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 6. 自定义返回数据处理示例
echo "6. 自定义返回数据处理示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    $result = $deepseekClient->chat('计算 12345 + 67890 的结果');
    
    // 自定义处理返回数据
    echo "原始响应数据：\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // 提取关键信息
    echo "\n处理后的响应：\n";
    echo "文本内容：" . $result['text'] . "\n";
    echo "模型名称：" . $result['model'] . "\n";
    echo "请求ID：" . $result['id'] . "\n";
    
    if (isset($result['usage'])) {
        echo "使用情况：\n";
        echo "- 提示词token数：" . ($result['usage']['prompt_tokens'] ?? 0) . "\n";
        echo "- 完成token数：" . ($result['usage']['completion_tokens'] ?? 0) . "\n";
        echo "- 总token数：" . ($result['usage']['total_tokens'] ?? 0) . "\n";
    }
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 8. 使用setPrepareRequestDataCallback自定义提交数据示例
echo "8. 使用setPrepareRequestDataCallback自定义提交数据示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    
    // 设置自定义请求数据准备回调
    $deepseekClient->setPrepareRequestDataCallback(function ($prompt, $options, $clientConfig) {
        // 自定义请求数据格式
        $data = [
            'model' => $clientConfig['model'] ?? 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => '你是一位专业的计算助手，只返回计算结果，不添加任何额外说明。'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $options['temperature'] ?? 0.1, // 降低温度以获得更准确的计算结果
            'max_tokens' => $options['max_tokens'] ?? 100,
            'top_p' => 0.9,
        ];
        
        // 打印自定义的请求数据
        echo "自定义请求数据：\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        return $data;
    });
    
    $result = $deepseekClient->chat('计算 999 * 999 的结果');
    echo "响应：" . $result['text'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 9. 使用setParseResponseCallback自定义返回数据示例
echo "9. 使用setParseResponseCallback自定义返回数据示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    
    // 设置自定义响应解析回调
    $deepseekClient->setParseResponseCallback(function ($response, $clientConfig) {
        // 打印原始响应数据
        echo "原始API响应：\n";
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // 自定义响应解析逻辑
        $customResponse = [
            'text' => $response['choices'][0]['message']['content'] ?? '无响应内容',
            'model' => $response['model'] ?? $clientConfig['model'],
            'id' => $response['id'] ?? '',
            'usage' => $response['usage'] ?? [],
            'custom_fields' => [
                'timestamp' => time(),
                'is_success' => isset($response['choices']) && count($response['choices']) > 0,
                'response_length' => strlen($response['choices'][0]['message']['content'] ?? ''),
                'model_info' => $clientConfig['model'] . ' by ' . $clientConfig['provider'],
            ]
        ];
        
        return $customResponse;
    });
    
    $result = $deepseekClient->chat('什么是PHP？');
    
    // 打印自定义解析后的响应
    echo "\n自定义解析后的响应：\n";
    echo "文本内容：" . $result['text'] . "\n";
    echo "模型名称：" . $result['model'] . "\n";
    echo "请求ID：" . $result['id'] . "\n";
    
    // 打印自定义字段
    echo "\n自定义字段：\n";
    echo "时间戳：" . date('Y-m-d H:i:s', $result['custom_fields']['timestamp']) . "\n";
    echo "是否成功：" . ($result['custom_fields']['is_success'] ? '是' : '否') . "\n";
    echo "响应长度：" . $result['custom_fields']['response_length'] . " 字符\n";
    echo "模型信息：" . $result['custom_fields']['model_info'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 10. 同时使用两个回调的示例
echo "10. 同时使用两个回调的示例：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    
    // 设置自定义请求数据准备回调
    $deepseekClient->setPrepareRequestDataCallback(function ($prompt, $options, $clientConfig) {
        return [
            'model' => $clientConfig['model'] ?? 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => '你是一位简洁的助手，只提供简短的回答。'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 100,
        ];
    });
    
    // 设置自定义响应解析回调
    $deepseekClient->setParseResponseCallback(function ($response, $clientConfig) {
        return [
            'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
            'model' => $response['model'] ?? 'unknown',
            'usage' => $response['usage'] ?? [],
            'processed_at' => date('Y-m-d H:i:s'),
        ];
    });
    
    $result = $deepseekClient->chat('如何快速学习PHP？');
    
    // 打印处理后的响应
    echo "处理后的响应：\n";
    echo "回答：" . $result['answer'] . "\n";
    echo "模型：" . $result['model'] . "\n";
    echo "处理时间：" . $result['processed_at'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 11. 错误处理示例
echo "11. 错误处理示例：\n";
try {
    // 使用错误的API密钥
    $errorConfig = $config['deepseek'];
    $errorConfig['api_key'] = 'invalid_api_key';
    
    $deepseekClient = AiClientFactory::create('deepseek', $errorConfig);
    $result = $deepseekClient->chat('你好');
    echo "响应：" . $result['text'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
    echo "错误处理：捕获到异常，程序可以继续执行\n";
}
echo "\n";

echo "示例执行完成！\n";