# PHP AI 插件

PHP AI 插件，整合文心、千问、火山、DeepSeek、混元大模型、智谱清言、Kimi、ChatGPT、Gemini、Minmax等AI模型接口，支持ThinkPHP和Laravel框架。

## 功能特性

- ✅ 支持多个AI模型：文心、千问、火山、DeepSeek、混元大模型、智谱清言、Kimi、ChatGPT、Gemini、Minmax
- ✅ 统一API接口，简化AI调用
- ✅ 支持流式响应
- ✅ 支持ThinkPHP 6.0+
- ✅ 支持Laravel 8.0+
- ✅ 支持Symfony 5.0+
- ✅ 支持Yii 2.0+
- ✅ 支持Slim 4.0+
- ✅ 支持Hyperf 2.0+
- ✅ 灵活的配置系统

## 安装

使用composer安装：

```bash
composer require phpais/ai-plugin
```

## 配置

### Laravel 配置

1. 发布配置文件：

```bash
php artisan vendor:publish --provider="Phpais\AiPlugin\Laravel\AiPluginServiceProvider"
```

2. 在 `.env` 文件中配置AI模型：

```env
# 默认AI模型
AI_DEFAULT=wenxin

# 文心AI配置
AI_WENXIN_API_KEY=your_api_key
AI_WENXIN_MODEL=ernie-bot

# 千问AI配置
AI_QIANWEN_API_KEY=your_api_key
AI_QIANWEN_MODEL=ep-20240101123456-abcde

# 火山AI配置
AI_VOLCANO_API_KEY=your_api_key
AI_VOLCANO_MODEL=ep-20240101123456-abcde

# DeepSeek AI配置
AI_DEEPSEEK_API_KEY=your_api_key
AI_DEEPSEEK_MODEL=deepseek-chat

# 混元大模型配置
AI_HUNYUAN_API_KEY=your_api_key
AI_HUNYUAN_MODEL=hunyuan-pro

# 智谱清言配置
AI_ZHIPU_API_KEY=your_api_key
AI_ZHIPU_MODEL=glm-4



# ChatGPT配置
AI_CHATGPT_API_KEY=your_api_key
AI_CHATGPT_MODEL=gpt-3.5-turbo

# Gemini配置
AI_GEMINI_API_KEY=your_api_key
AI_GEMINI_MODEL=gemini-pro

# Minmax配置
AI_MINMAX_API_KEY=your_api_key
AI_MINMAX_MODEL=abab5.5-chat

# Kimi配置
AI_KIMI_API_KEY=your_api_key
AI_KIMI_MODEL=kimi
```

### ThinkPHP 配置

1. 在 `config` 目录下创建 `ai.php` 配置文件，内容参考 `src/Config/ai.php`

2. 在 `.env` 文件中配置AI模型，配置项与Laravel相同

### Symfony 配置

1. 注册Bundle到 `config/bundles.php`：

```php
return [
    // 其他Bundle
    Phpais\AiPlugin\Symfony\AiPluginBundle::class => ['all' => true],
];
```

2. 在 `.env` 文件中配置AI模型，配置项与Laravel相同

### Yii 配置

1. 注册Module到应用配置：

```php
// config/web.php 或 config/console.php
return [
    'modules' => [
        'aiPlugin' => [
            'class' => Phpais\AiPlugin\Yii\AiPluginModule::class,
        ],
    ],
];
```

2. 在 `.env` 文件中配置AI模型，配置项与Laravel相同

### Slim 配置

1. 注册服务到Slim应用：

```php
use Phpais\AiPlugin\Slim\AiPluginProvider;

$app = AppFactory::create();

// 注册AI插件服务
$app->add(AiPluginProvider::class);
```

2. 在 `.env` 文件中配置AI模型，配置项与Laravel相同

### Hyperf 配置

1. 注册Provider到 `config/autoload/provider.php`：

```php
return [
    // 其他Provider
    Phpais\AiPlugin\Hyperf\Provider\AiPluginServiceProvider::class,
];
```

2. 在 `.env` 文件中配置AI模型，配置项与Laravel相同

## 使用示例

### Laravel 示例

#### 基本使用

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 发送文本请求
$result = AI::chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 带参数的请求
$result = AI::chat('写一首关于春天的诗', [
    'temperature' => 0.8,
    'max_tokens' => 512,
    'system' => '你是一位诗人，擅长写抒情诗'
]);
echo $result['text'];

// 流式响应
AI::streamChat('写一篇关于AI的文章', function ($chunk) {
    echo $chunk;
    flush();
});
```

#### 使用特定模型

```php
use Phpais\AiPlugin\Factories\AiClientFactory;

// 使用千问模型
$qianwenClient = AiClientFactory::create('qianwen', config('ai.providers.qianwen'));
$result = $qianwenClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用DeepSeek模型
$deepseekClient = AiClientFactory::create('deepseek', config('ai.providers.deepseek'));
$result = $deepseekClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用混元大模型
$hunyuanClient = AiClientFactory::create('hunyuan', config('ai.providers.hunyuan'));
$result = $hunyuanClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用智谱清言
$zhipuClient = AiClientFactory::create('zhipu', config('ai.providers.zhipu'));
$result = $zhipuClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];



// 使用ChatGPT
$chatgptClient = AiClientFactory::create('chatgpt', config('ai.providers.chatgpt'));
$result = $chatgptClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用Gemini
$geminiClient = AiClientFactory::create('gemini', config('ai.providers.gemini'));
$result = $geminiClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用Minmax
$minmaxClient = AiClientFactory::create('minmax', config('ai.providers.minmax'));
$result = $minmaxClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用Kimi
$kimiClient = AiClientFactory::create('kimi', config('ai.providers.kimi'));
$result = $kimiClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];
```

### ThinkPHP 示例

#### 基本使用

```php
// 发送文本请求
$result = app('ai')->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 带参数的请求
$result = app('ai')->chat('写一首关于春天的诗', [
    'temperature' => 0.8,
    'max_tokens' => 512,
    'system' => '你是一位诗人，擅长写抒情诗'
]);
echo $result['text'];

// 流式响应
app('ai')->streamChat('写一篇关于AI的文章', function ($chunk) {
    echo $chunk;
    flush();
});
```

#### 使用特定模型

```php
use Phpais\AiPlugin\Factories\AiClientFactory;

// 使用千问模型
$qianwenClient = AiClientFactory::create('qianwen', config('ai.providers.qianwen'));
$result = $qianwenClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用DeepSeek模型
$deepseekClient = AiClientFactory::create('deepseek', config('ai.providers.deepseek'));
$result = $deepseekClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用混元大模型
$hunyuanClient = AiClientFactory::create('hunyuan', config('ai.providers.hunyuan'));
$result = $hunyuanClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 使用智谱清言
$zhipuClient = AiClientFactory::create('zhipu', config('ai.providers.zhipu'));
$result = $zhipuClient->chat('你好，能介绍一下你自己吗？');
echo $result['text'];


```

### Symfony 示例

#### 基本使用

```php
// 注入AI服务
public function index(Phpais\AiPlugin\Contracts\AiClientInterface $aiClient)
{
    // 发送文本请求
    $result = $aiClient->chat('你好，能介绍一下你自己吗？');
    echo $result['text'];

    // 带参数的请求
    $result = $aiClient->chat('写一首关于春天的诗', [
        'temperature' => 0.8,
        'max_tokens' => 512,
        'system' => '你是一位诗人，擅长写抒情诗'
    ]);
    echo $result['text'];

    // 流式响应
    $aiClient->streamChat('写一篇关于AI的文章', function ($chunk) {
        echo $chunk;
        flush();
    });
}
```

### Yii 示例

#### 基本使用

```php
// 发送文本请求
$result = Yii::$app->get('ai')->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 带参数的请求
$result = Yii::$app->get('ai')->chat('写一首关于春天的诗', [
    'temperature' => 0.8,
    'max_tokens' => 512,
    'system' => '你是一位诗人，擅长写抒情诗'
]);
echo $result['text'];

// 流式响应
Yii::$app->get('ai')->streamChat('写一篇关于AI的文章', function ($chunk) {
    echo $chunk;
    flush();
});
```

### Slim 示例

#### 基本使用

```php
// 发送文本请求
$result = $app->get('ai')->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 带参数的请求
$result = $app->get('ai')->chat('写一首关于春天的诗', [
    'temperature' => 0.8,
    'max_tokens' => 512,
    'system' => '你是一位诗人，擅长写抒情诗'
]);
echo $result['text'];

// 流式响应
$app->get('ai')->streamChat('写一篇关于AI的文章', function ($chunk) {
    echo $chunk;
    flush();
});
```

### Hyperf 示例

#### 基本使用

```php
// 注入AI服务
public function index(Phpais\AiPlugin\Contracts\AiClientInterface $aiClient)
{
    // 发送文本请求
    $result = $aiClient->chat('你好，能介绍一下你自己吗？');
    echo $result['text'];

    // 带参数的请求
    $result = $aiClient->chat('写一首关于春天的诗', [
        'temperature' => 0.8,
        'max_tokens' => 512,
        'system' => '你是一位诗人，擅长写抒情诗'
    ]);
    echo $result['text'];

    // 流式响应
    $aiClient->streamChat('写一篇关于AI的文章', function ($chunk) {
        echo $chunk;
        flush();
    });
}
```

## API 文档

### 核心方法

#### `chat(string $prompt, array $options = []): array`

发送文本请求到AI模型

- `$prompt`: 提示词
- `$options`: 可选参数，包括 temperature, max_tokens, system 等
- 返回值: 包含响应文本和使用信息的数组

#### `streamChat(string $prompt, callable $callback, array $options = []): void`

发送流式文本请求到AI模型

- `$prompt`: 提示词
- `$callback`: 回调函数，用于处理流式响应
- `$options`: 可选参数

#### `generateImage(string $prompt, array $options = []): array`

生成图像（部分模型支持）

- `$prompt`: 提示词
- `$options`: 可选参数
- 返回值: 包含图像URL和使用信息的数组

#### `getModelInfo(): array`

获取模型信息

- 返回值: 模型信息数组

#### `setPrepareRequestDataCallback(callable $callback): $this`

设置自定义请求数据准备回调函数

- `$callback`: 接收 `$prompt`、`$options`、`$clientConfig` 参数，返回自定义请求数据数组
- 返回值: 当前客户端实例，支持链式调用

**使用示例**:

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 获取客户端实例
$aiClient = app('ai');

// 设置自定义请求数据回调
$aiClient->setPrepareRequestDataCallback(function ($prompt, $options, $clientConfig) {
    return [
        'model' => $clientConfig['model'] ?? 'deepseek-chat',
        'messages' => [
            [
                'role' => 'system',
                'content' => '你是一位专业的助手，只提供准确的信息。'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => $options['temperature'] ?? 0.7,
        'max_tokens' => $options['max_tokens'] ?? 1024,
        'top_p' => 0.9,
    ];
});

// 正常调用
$result = $aiClient->chat('什么是PHP？');
echo $result['text'];
```

#### `setParseResponseCallback(callable $callback): $this`

设置自定义响应解析回调函数

- `$callback`: 接收 `$response`、`$clientConfig` 参数，返回自定义响应数据数组
- 返回值: 当前客户端实例，支持链式调用

**使用示例**:

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 获取客户端实例
$aiClient = app('ai');

// 设置自定义响应解析回调
$aiClient->setParseResponseCallback(function ($response, $clientConfig) {
    return [
        'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
        'model' => $response['model'] ?? $clientConfig['model'],
        'usage' => $response['usage'] ?? [],
        'custom_fields' => [
            'timestamp' => time(),
            'response_length' => strlen($response['choices'][0]['message']['content'] ?? ''),
            'is_success' => isset($response['choices']) && count($response['choices']) > 0,
        ]
    ];
});

// 正常调用
$result = $aiClient->chat('什么是PHP？');
echo $result['answer'];
echo '处理时间: ' . date('Y-m-d H:i:s', $result['custom_fields']['timestamp']);
```

## 支持的AI模型

| 模型 | 提供者 | 配置键 | 说明 |
|------|--------|--------|------|
| 文心一言 | 百度 | wenxin | 百度的AI模型 |
| 千问 | 阿里 | qianwen | 阿里的AI模型 |
| 火山 | 字节跳动 | volcano | 字节跳动的AI模型 |
| DeepSeek | DeepSeek | deepseek | DeepSeek的AI模型 |
| 混元大模型 | 腾讯 | hunyuan | 腾讯的AI模型 |
| 智谱清言 | 智谱AI | zhipu | 智谱AI的AI模型 |
| Kimi | 月之暗面 | kimi | 月之暗面的AI模型 |
| ChatGPT | OpenAI | chatgpt | OpenAI的AI模型 |
| Gemini | Google | gemini | Google的AI模型 |
| Minmax | Minmax | minmax | Minmax的AI模型 |

## 注意事项

1. 请确保在使用前配置好对应的API密钥
2. 不同模型的API调用方式和参数可能略有差异
3. 流式响应需要在支持的环境中使用

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request！
