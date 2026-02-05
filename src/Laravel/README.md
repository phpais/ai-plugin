# Laravel 框架使用说明

## 安装配置

### 1. 注册服务提供者

在 `config/app.php` 文件中注册服务提供者：

```php
return [
    // 其他配置...
    'providers' => [
        // 其他服务提供者...
        Phpais\AiPlugin\Laravel\AiPluginServiceProvider::class,
    ],
    // 其他配置...
];
```

### 2. 注册门面（可选）

在 `config/app.php` 文件中注册门面：

```php
return [
    // 其他配置...
    'aliases' => [
        // 其他门面...
        'AI' => Phpais\AiPlugin\Laravel\Facades\AI::class,
    ],
    // 其他配置...
];
```

### 3. 发布配置文件

运行以下命令发布配置文件：

```bash
php artisan vendor:publish --tag=config
```

### 4. 配置环境变量

在 `.env` 文件中添加配置：

```env
# AI 配置
AI_DEFAULT=deepseek

# DeepSeek 配置
AI_DEEPSEEK_API_KEY=your_api_key
AI_DEEPSEEK_MODEL=deepseek-chat
AI_DEEPSEEK_ENDPOINT=https://api.deepseek.com/chat/completions
AI_DEEPSEEK_TIMEOUT=30

# 其他模型配置...
```

## 使用示例

### 1. 基本调用

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 基本调用
$result = AI::chat('你好，能介绍一下你自己吗？');
echo $result['text'];
```

### 2. 带参数调用

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 带参数调用
$result = AI::chat('写一首关于春天的诗', [
    'temperature' => 0.8, // 温度参数，控制生成文本的随机性
    'max_tokens' => 512, // 最大生成 tokens 数
    'system' => '你是一位诗人，擅长写抒情诗', // 系统提示，定义 AI 角色
]);
echo $result['text'];
```

### 3. 流式响应

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 流式响应
AI::streamChat('写一篇关于 Laravel 的文章', function ($chunk) {
    // 实时输出 AI 生成的内容
    echo $chunk;
    flush(); // 刷新输出缓冲区
});
```

### 4. 自定义回调

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 获取客户端实例以设置回调
$aiClient = app('ai');

// 自定义请求数据准备
$aiClient->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
    return [
        'model' => $config['model'],
        'messages' => [
            ['role' => 'system', 'content' => '你是一位专业的 Laravel 开发者'],
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
$result = $aiClient->chat('如何在 Laravel 中实现缓存？');
echo $result['answer'];
echo '处理时间: ' . $result['processed_at'];
```

### 5. 错误处理

```php
use Phpais\AiPlugin\Laravel\Facades\AI;
try {
    $result = AI::chat('你好');
    echo $result['text'];
} catch (\Exception $e) {
    echo '错误信息: ' . $e->getMessage();
    // 记录错误日志
    logger()->error('AI 调用失败', ['error' => $e->getMessage()]);
}
```

## 常见问题

### 1. 服务未注册

**问题**：`AI` 门面未找到或 `app('ai')` 返回 null

**解决方案**：确保已正确注册服务提供者和门面

### 2. 配置未加载

**问题**：API 密钥未生效

**解决方案**：确保 `.env` 文件中的配置正确，并且已运行 `php artisan config:cache` 清除配置缓存

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

## 高级用法

### 切换模型

```php
// 使用文心模型
$wenxinClient = app('ai.factory')->create('wenxin', config('ai.providers.wenxin'));
$result = $wenxinClient->chat('你好');

// 使用千问模型
$qianwenClient = app('ai.factory')->create('qianwen', config('ai.providers.qianwen'));
$result = $qianwenClient->chat('你好');
```

### 自定义模型配置

```php
// 创建自定义配置的客户端
$customConfig = [
    'api_key' => 'your_custom_key',
    'model' => 'custom-model',
    'endpoint' => 'https://custom-api.example.com/chat/completions',
    'timeout' => 30,
    'provider' => 'custom',
];

$customClient = app('ai.factory')->create('deepseek', $customConfig);
$result = $customClient->chat('你好');
```