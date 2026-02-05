# Hyperf 框架使用说明

## 安装配置

### 1. 安装依赖

使用 Composer 安装 PHP AI 插件：

```bash
composer require phpaid/ai-plugin
```

### 2. 注册服务提供者

在 `config/autoload/dependencies.php` 文件中添加：

```php
return [
    // 其他依赖
    Phpais\AiPlugin\Hyperf\Provider\AiPluginServiceProvider::class,
];
```

### 3. 创建配置文件

在 `config/autoload` 目录下创建 `ai.php` 文件：

```php
return [
    'default' => env('AI_DEFAULT', 'deepseek'),
    'providers' => [
        'deepseek' => [
            'api_key' => env('AI_DEEPSEEK_API_KEY', 'your_api_key'),
            'model' => env('AI_DEEPSEEK_MODEL', 'deepseek-chat'),
            'endpoint' => env('AI_DEEPSEEK_ENDPOINT', 'https://api.deepseek.com/chat/completions'),
            'timeout' => (int)env('AI_DEEPSEEK_TIMEOUT', 30),
            'provider' => 'deepseek',
        ],
        // 其他模型配置...
        'wenxin' => [
            'api_key' => env('AI_WENXIN_API_KEY', ''),
            'model' => env('AI_WENXIN_MODEL', 'ernie-bot'),
            'endpoint' => env('AI_WENXIN_ENDPOINT', 'https://qianfan.baidubce.com/v2/chat/completions'),
            'timeout' => (int)env('AI_WENXIN_TIMEOUT', 30),
            'provider' => 'wenxin',
        ],
    ],
];
```

### 4. 配置环境变量

在 `.env` 文件中添加：

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

### 5. 清除缓存

运行以下命令清除缓存：

```bash
php bin/hyperf.php clear
```

## 使用示例

### 1. 基本调用

```php
// 在控制器中使用
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @Controller(prefix="/chat")
 */
class ChatController
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    /**
     * @GetMapping("/index")
     */
    public function index()
    {
        $result = $this->ai->chat('什么是PHP？');
        return $result['text'];
    }
}
```

### 2. 带参数调用

```php
// 带参数调用
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller(prefix="/chat")
 */
class ChatController
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    /**
     * @PostMapping("/send")
     */
    public function send(RequestInterface $request)
    {
        $prompt = $request->input('prompt', '你好');
        
        $result = $this->ai->chat($prompt, [
            'temperature' => 0.8, // 温度参数，控制生成文本的随机性
            'max_tokens' => 512, // 最大生成 tokens 数
            'system' => '你是一位诗人，擅长写抒情诗', // 系统提示，定义 AI 角色
        ]);
        
        return ['result' => $result['text']];
    }
}
```

### 3. 流式响应

```php
// 流式响应
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * @Controller(prefix="/chat")
 */
class ChatController
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    /**
     * @Inject()
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @GetMapping("/stream")
     */
    public function stream()
    {
        $prompt = '写一篇关于 Hyperf 的文章';
        
        // 设置响应头
        $response = $this->response->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('Connection', 'keep-alive');
        
        // 使用 Swoole 协程流式输出
        $response->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(function () use ($prompt) {
            $this->ai->streamChat($prompt, function ($chunk) {
                echo $chunk;
                flush();
            });
        }));
        
        return $response;
    }
}
```

### 4. 自定义回调

```php
// 自定义回调
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @Controller(prefix="/chat")
 */
class ChatController
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    /**
     * @GetMapping("/custom")
     */
    public function custom()
    {
        // 自定义请求数据准备
        $this->ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
            return [
                'model' => $config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => '你是一位专业的 Hyperf 开发者'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1024,
            ];
        });
        
        // 自定义响应解析
        $this->ai->setParseResponseCallback(function ($response, $config) {
            return [
                'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
                'model' => $response['model'] ?? 'unknown',
                'processed_at' => date('Y-m-d H:i:s'),
            ];
        });
        
        // 调用
        $result = $this->ai->chat('如何在 Hyperf 中实现缓存？');
        return $result;
    }
}
```

### 5. 错误处理

```php
// 错误处理
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * @Controller(prefix="/chat")
 */
class ChatController
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @GetMapping("/error")
     */
    public function error()
    {
        try {
            $result = $this->ai->chat('你好');
            return $result['text'];
        } catch (\Exception $e) {
            // 记录错误日志
            $this->logger->error('AI 调用失败', ['error' => $e->getMessage()]);
            
            return ['error' => $e->getMessage()];
        }
    }
}
```

## 完整控制器示例

### 示例控制器

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * @Controller(prefix="/ai")
 */
class AIController
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    /**
     * @Inject()
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject()
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * 基本聊天
     * @GetMapping("/chat")
     */
    public function chat()
    {
        $prompt = $this->request->input('prompt', '你好');
        
        try {
            $result = $this->ai->chat($prompt);
            return ['prompt' => $prompt, 'result' => $result['text']];
        } catch (\Exception $e) {
            $this->logger->error('AI 聊天失败', ['error' => $e->getMessage()]);
            return ['error' => '聊天失败，请稍后重试'];
        }
    }

    /**
     * 高级聊天
     * @PostMapping("/advanced")
     */
    public function advanced()
    {
        $prompt = $this->request->input('prompt', '写一篇关于技术的文章');
        $temperature = (float)$this->request->input('temperature', 0.7);
        $maxTokens = (int)$this->request->input('max_tokens', 500);
        $system = $this->request->input('system', '你是一位技术专家');
        
        try {
            $result = $this->ai->chat($prompt, [
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'system' => $system,
            ]);
            return ['prompt' => $prompt, 'result' => $result['text']];
        } catch (\Exception $e) {
            $this->logger->error('AI 高级聊天失败', ['error' => $e->getMessage()]);
            return ['error' => '聊天失败，请稍后重试'];
        }
    }

    /**
     * 流式响应
     * @GetMapping("/stream")
     */
    public function stream()
    {
        $prompt = $this->request->input('prompt', '写一篇关于 Hyperf 框架的技术文章');
        
        try {
            $response = $this->response->withHeader('Content-Type', 'text/event-stream')
                ->withHeader('Cache-Control', 'no-cache')
                ->withHeader('Connection', 'keep-alive');
            
            $response->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(function () use ($prompt) {
                $this->ai->streamChat($prompt, function ($chunk) {
                    echo $chunk;
                    flush();
                });
            }));
            
            return $response;
        } catch (\Exception $e) {
            $this->logger->error('AI 流式响应失败', ['error' => $e->getMessage()]);
            return $this->response->withJson(['error' => '流式响应失败'])->withStatus(500);
        }
    }

    /**
     * 自定义回调
     * @GetMapping("/custom")
     */
    public function custom()
    {
        $prompt = $this->request->input('prompt', '如何优化 Hyperf 应用性能？');
        
        try {
            // 自定义请求数据
            $this->ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
                return [
                    'model' => $config['model'],
                    'messages' => [
                        ['role' => 'system', 'content' => '你是一位专业的 Hyperf 性能优化专家'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.6,
                    'max_tokens' => 800,
                ];
            });
            
            // 自定义响应解析
            $this->ai->setParseResponseCallback(function ($response, $config) {
                return [
                    'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
                    'model' => $response['model'] ?? 'unknown',
                    'usage' => $response['usage'] ?? [],
                    'timestamp' => time(),
                ];
            });
            
            $result = $this->ai->chat($prompt);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('AI 自定义回调失败', ['error' => $e->getMessage()]);
            return ['error' => '处理失败，请稍后重试'];
        }
    }

    /**
     * 切换模型
     * @GetMapping("/switch")
     */
    public function switch()
    {
        try {
            // 获取工厂类
            $factory = make(\Phpais\AiPlugin\Factories\AiClientFactory::class);
            
            // 从配置中获取不同模型的配置
            $config = config('ai');
            
            // 创建文心客户端
            $wenxinConfig = $config['providers']['wenxin'] ?? [];
            if (!empty($wenxinConfig)) {
                $wenxinClient = $factory->create('wenxin', $wenxinConfig);
                $wenxinResult = $wenxinClient->chat('你好');
            }
            
            // 创建千问客户端
            $qianwenConfig = $config['providers']['qianwen'] ?? [];
            if (!empty($qianwenConfig)) {
                $qianwenClient = $factory->create('qianwen', $qianwenConfig);
                $qianwenResult = $qianwenClient->chat('你好');
            }
            
            return [
                'wenxin' => $wenxinResult['text'] ?? '未配置',
                'qianwen' => $qianwenResult['text'] ?? '未配置',
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI 模型切换失败', ['error' => $e->getMessage()]);
            return ['error' => '模型切换失败，请稍后重试'];
        }
    }
}
```

## 常见问题

### 1. 服务未注册

**问题**：`make('ai')` 或 `@Inject()` 注入失败

**解决方案**：确保已在 `config/autoload/dependencies.php` 文件中正确注册服务提供者

### 2. 配置未加载

**问题**：API 密钥未生效

**解决方案**：确保 `.env` 文件中的配置正确，并且已运行 `php bin/hyperf.php config:cache` 清除配置缓存

### 3. 流式响应不工作

**问题**：流式响应未实时输出

**解决方案**：确保服务器支持流式输出，并且在回调函数中使用 `flush()` 刷新输出缓冲区

### 4. 超时问题

**问题**：请求超时

**解决方案**：在配置文件中增加超时时间：

```php
// config/autoload/ai.php
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

**解决方案**：修改配置文件中的 `default` 值：

```php
// config/autoload/ai.php
return [
    'default' => 'wenxin', // 切换到文心模型
    // 其他配置...
];
```

## 高级用法

### 手动创建客户端

```php
use Phpais\AiPlugin\Factories\AiClientFactory;

// 创建不同模型的客户端
$factory = make(AiClientFactory::class);

// 创建文心客户端
$wenxinConfig = config('ai.providers.wenxin');
$wenxinClient = $factory->create('wenxin', $wenxinConfig);
$result1 = $wenxinClient->chat('你好');

// 创建千问客户端
$qianwenConfig = config('ai.providers.qianwen');
$qianwenClient = $factory->create('qianwen', $qianwenConfig);
$result2 = $qianwenClient->chat('你好');
```

### 使用事件监听

```php
// 在事件监听器中使用 AI
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class AIChatListener implements ListenerInterface
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    public function listen(): array
    {
        return [
            // 监听的事件
        ];
    }

    public function process(object $event)
    {
        // 使用 AI 进行处理
        $result = $this->ai->chat('处理事件: ' . get_class($event));
        // 处理结果...
    }
}
```

### 缓存 AI 响应

```php
use Hyperf\Cache\Annotation\Cacheable;

class AIService
{
    /**
     * @Inject()
     * @var \Phpais\AiPlugin\Abstracts\AiClient
     */
    protected $ai;

    /**
     * @Cacheable(prefix="ai_response", ttl=3600)
     */
    public function getChatResponse(string $prompt)
    {
        return $this->ai->chat($prompt);
    }
}
```