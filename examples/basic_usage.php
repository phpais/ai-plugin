<?php

require __DIR__ . '/../vendor/autoload.php';

use Phpais\AiPlugin\Factories\AiClientFactory;

// 配置示例
$config = [
    'wenxin' => [
        'api_key' => 'your_wenxin_api_key',
        'model' => 'ernie-bot',
        'endpoint' => 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions',
        'timeout' => 30,
        'provider' => 'wenxin',
    ],
    'qianwen' => [
        'api_key' => 'your_qianwen_api_key',
        'model' => 'ep-20240101123456-abcde',
        'endpoint' => 'https://ark.cn-beijing.volces.com/api/v3/chat/completions',
        'timeout' => 30,
        'provider' => 'qianwen',
    ],
    'volcano' => [
        'api_key' => 'your_volcano_api_key',
        'model' => 'ep-20240101123456-abcde',
        'endpoint' => 'https://ark.cn-beijing.volces.com/api/v3/chat/completions',
        'timeout' => 30,
        'provider' => 'volcano',
    ],
    'deepseek' => [
        'api_key' => 'your_deepseek_api_key',
        'model' => 'deepseek-chat',
        'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
        'timeout' => 30,
        'provider' => 'deepseek',
    ],
    'hunyuan' => [
        'api_key' => 'your_hunyuan_api_key',
        'model' => 'hunyuan-pro',
        'endpoint' => 'https://api.hunyuan.cn/v1/chat/completions',
        'timeout' => 30,
        'provider' => 'hunyuan',
    ],
    'zhipu' => [
        'api_key' => 'your_zhipu_api_key',
        'model' => 'glm-4',
        'endpoint' => 'https://open.bigmodel.cn/api/mcp/v1/chat/completions',
        'timeout' => 30,
        'provider' => 'zhipu',
    ],
    'moonshot' => [
        'api_key' => 'your_moonshot_api_key',
        'model' => 'moonshot-v1-8k',
        'endpoint' => 'https://api.moonshot.cn/v1/chat/completions',
        'timeout' => 30,
        'provider' => 'moonshot',
    ],
];

echo "PHP AI 插件使用示例\n";
echo "======================\n\n";

// 使用文心模型
echo "1. 使用文心模型：\n";
try {
    $wenxinClient = AiClientFactory::create('wenxin', $config['wenxin']);
    $result = $wenxinClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 使用千问模型
echo "2. 使用千问模型：\n";
try {
    $qianwenClient = AiClientFactory::create('qianwen', $config['qianwen']);
    $result = $qianwenClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 使用火山模型
echo "3. 使用火山模型：\n";
try {
    $volcanoClient = AiClientFactory::create('volcano', $config['volcano']);
    $result = $volcanoClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 使用DeepSeek模型
echo "4. 使用DeepSeek模型：\n";
try {
    $deepseekClient = AiClientFactory::create('deepseek', $config['deepseek']);
    $result = $deepseekClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 带参数的请求
echo "5. 带参数的请求：\n";
try {
    $wenxinClient = AiClientFactory::create('wenxin', $config['wenxin']);
    $result = $wenxinClient->chat('写一首关于春天的诗', [
        'temperature' => 0.8,
        'max_tokens' => 512,
        'system' => '你是一位诗人，擅长写抒情诗'
    ]);
    echo "响应：" . $result['text'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 使用混元大模型
echo "6. 使用混元大模型：\n";
try {
    $hunyuanClient = AiClientFactory::create('hunyuan', $config['hunyuan']);
    $result = $hunyuanClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 使用智谱清言
echo "7. 使用智谱清言：\n";
try {
    $zhipuClient = AiClientFactory::create('zhipu', $config['zhipu']);
    $result = $zhipuClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

// 使用月之暗面
echo "8. 使用月之暗面：\n";
try {
    $moonshotClient = AiClientFactory::create('moonshot', $config['moonshot']);
    $result = $moonshotClient->chat('你好，能介绍一下你自己吗？');
    echo "响应：" . $result['text'] . "\n";
    echo "模型：" . $result['model'] . "\n";
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
echo "\n";

echo "示例执行完成！\n";
