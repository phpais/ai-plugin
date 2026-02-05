# ThinkPHP 框架使用说明

## 安装配置

### 1. 注册服务

在 `config/services.php` 文件中注册 AI 服务：

```php
return [
    // 其他服务...
    'ai' => [
        'class' => Phpais\AiPlugin\ThinkPHP\Service::class,
    ],
];
```

### 2. 创建配置文件

在 `config` 目录下创建 `ai.php` 文件：

```php
return [
    'default' => 'deepseek', // 默认使用的模型
    'providers' => [
        'deepseek' => [
            'api_key' => env('AI_DEEPSEEK_API_KEY', 'your_api_key'), // API 密钥
            'model' => env('AI_DEEPSEEK_MODEL', 'deepseek-chat'), // 模型名称
            'endpoint' => env('AI_DEEPSEEK_ENDPOINT', 'https://api.deepseek.com/chat/completions'), // API 端点
            'timeout' => env('AI_DEEPSEEK_TIMEOUT', 30), // 超时时间
            'provider' => 'deepseek', // 提供者名称
        ],
        // 可添加其他模型配置...
        'wenxin' => [
            'api_key' => env('AI_WENXIN_API_KEY', ''),
            'model' => env('AI_WENXIN_MODEL', 'ernie-bot'),
            'endpoint' => env('AI_WENXIN_ENDPOINT', 'https://qianfan.baidubce.com/v2/chat/completions'),
            'timeout' => env('AI_WENXIN_TIMEOUT', 30),
            'provider' => 'wenxin',
        ],
    ],
];
```

### 3. 配置环境变量（推荐）

在 `.env` 文件中添加 API 密钥等敏感信息：

```env
# AI 配置
AI_DEEPSEEK_API_KEY=your_deepseek_api_key
AI_DEEPSEEK_MODEL=deepseek-chat
AI_DEEPSEEK_ENDPOINT=https://api.deepseek.com/chat/completions
AI_DEEPSEEK_TIMEOUT=30

# 其他模型配置...
```

## 使用示例

### 1. 基本调用

```php
// 基本调用
$result = app('ai')->chat('你好，能介绍一下你自己吗？');
echo $result['text'];
```

### 2. 带参数调用

```php
// 带参数调用
$result = app('ai')->chat('写一首关于春天的诗', [
    'temperature' => 0.8, // 温度参数，控制生成文本的随机性
    'max_tokens' => 512, // 最大生成 tokens 数
    'system' => '你是一位诗人，擅长写抒情诗', // 系统提示，定义 AI 角色
]);
echo $result['text'];
```

### 3. 流式响应

```php
// 流式响应
app('ai')->streamChat('写一篇关于 ThinkPHP 的文章', function ($chunk) {
    // 实时输出 AI 生成的内容
    echo $chunk;
    flush(); // 刷新输出缓冲区
});
```

### 4. 自定义回调

```php
// 获取客户端实例以设置回调
$aiClient = app('ai');

// 自定义请求数据准备
$aiClient->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
    return [
        'model' => $config['model'],
        'messages' => [
            ['role' => 'system', 'content' => '你是一位专业的 ThinkPHP 开发者'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 1024,
    ];
});

// 自定义响应解析
$aiClient->setParseResponseCallback(function ($response, $config) {
    return [
        'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
        'model' => $response['model'] ?? 'unknown',
        'processed_at' => date('Y-m-d H:i:s'),
    ];
});

// 调用
$result = $aiClient->chat('如何在 ThinkPHP 中实现缓存？');
echo $result['answer'];
echo '处理时间: ' . $result['processed_at'];
```

### 5. 错误处理

```php
try {
    $result = app('ai')->chat('你好');
    echo $result['text'];
} catch (\Exception $e) {
    echo '错误信息: ' . $e->getMessage();
    // 记录错误日志
    \think\facade\Log::error('AI 调用失败', ['error' => $e->getMessage()]);
}
```

## 控制器示例

### 完整的控制器示例

```php
<?php

namespace app\controller;

use think\Controller;

class AIController extends Controller
{
    // 基本聊天示例
    public function chat()
    {
        $prompt = $this->request->param('prompt', '你好');
        $result = app('ai')->chat($prompt);
        return json(['result' => $result['text']]);
    }

    // 流式响应示例
    public function stream()
    {
        $prompt = $this->request->param('prompt', '写一篇关于 PHP 的文章');
        
        // 禁用输出缓冲
        ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        app('ai')->streamChat($prompt, function ($chunk) {
            echo $chunk;
            flush();
            ob_flush();
        });
        
        return '';
    }

    // 自定义回调示例
    public function custom()
    {
        $ai = app('ai');
        
        // 自定义请求数据
        $ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
            return [
                'model' => $config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => '你是一位简洁的助手，只提供简短回答'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.5,
                'max_tokens' => 200,
            ];
        });
        
        // 自定义响应解析
        $ai->setParseResponseCallback(function ($response, $config) {
            return [
                'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
                'model' => $response['model'],
                'time' => date('Y-m-d H:i:s'),
            ];
        });
        
        $result = $ai->chat('如何快速学习 ThinkPHP？');
        return json($result);
    }
}
```

## 常见问题

### 1. 服务未注册

**问题**：`app('ai')` 返回 null

**解决方案**：确保已在 `config/services.php` 文件中正确注册 AI 服务

### 2. 配置未加载

**问题**：API 密钥未生效

**解决方案**：确保 `config/ai.php` 文件存在且配置正确，并且已运行 `php think clear` 清除缓存

### 3. 流式响应不工作

**问题**：流式响应未实时输出

**解决方案**：确保服务器支持流式输出，并且在回调函数中使用 `flush()` 刷新输出缓冲区

### 4. 超时问题

**问题**：请求超时

**解决方案**：在配置文件中增加超时时间：

```php
// config/ai.php
return [
    'providers' => [
        'deepseek' => [
            // 其他配置...
            'timeout' => 60, // 增加超时时间
        ],
    ],
];
```

### 5. 模型切换

**问题**：如何切换不同的 AI 模型

**解决方案**：修改 `config/ai.php` 文件中的 `'default'` 值，例如切换到文心模型：

```php
return [
    'default' => 'wenxin', // 切换到文心模型
    // 其他配置...
];
```

## 高级用法

### 手动创建客户端

```php
// 手动创建 DeepSeek 客户端
$deepseekConfig = config('ai.providers.deepseek');
$deepseekClient = app('ai.factory')->create('deepseek', $deepseekConfig);
$result = $deepseekClient->chat('你好');

// 手动创建文心客户端
$wenxinConfig = config('ai.providers.wenxin');
$wenxinClient = app('ai.factory')->create('wenxin', $wenxinConfig);
$result = $wenxinClient->chat('你好');
```

### 多轮对话

```php
// 多轮对话示例
$ai = app('ai');

// 第一轮
$result1 = $ai->chat('你好，我叫张三');
echo 'AI: ' . $result1['text'] . "\n";

// 第二轮
$result2 = $ai->chat('我想学习 PHP，有什么建议吗？');
echo 'AI: ' . $result2['text'] . "\n";

// 注意：默认情况下，每次调用都是独立的对话
// 如果需要上下文连续的对话，需要在每次调用时传递历史记录
```