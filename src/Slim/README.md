# Slim 框架使用说明

## 安装配置

### 1. 安装依赖

使用 Composer 安装 PHP AI 插件：

```bash
composer require phpaid/ai-plugin
```

### 2. 注册服务

在应用初始化时注册 AI 服务：

```php
use Slim\Factory\AppFactory;
use Phpais\AiPlugin\Slim\AiPluginProvider;

// 创建 Slim 应用实例
$app = AppFactory::create();

// 配置 AI 插件
$config = [
    'default' => 'deepseek',
    'providers' => [
        'deepseek' => [
            'api_key' => getenv('AI_DEEPSEEK_API_KEY') ?: 'your_api_key',
            'model' => getenv('AI_DEEPSEEK_MODEL') ?: 'deepseek-chat',
            'endpoint' => getenv('AI_DEEPSEEK_ENDPOINT') ?: 'https://api.deepseek.com/chat/completions',
            'timeout' => (int)(getenv('AI_DEEPSEEK_TIMEOUT') ?: 30),
            'provider' => 'deepseek',
        ],
        // 其他模型配置...
        'wenxin' => [
            'api_key' => getenv('AI_WENXIN_API_KEY') ?: '',
            'model' => getenv('AI_WENXIN_MODEL') ?: 'ernie-bot',
            'endpoint' => getenv('AI_WENXIN_ENDPOINT') ?: 'https://qianfan.baidubce.com/v2/chat/completions',
            'timeout' => (int)(getenv('AI_WENXIN_TIMEOUT') ?: 30),
            'provider' => 'wenxin',
        ],
    ],
];

// 注册 AI 插件
AiPluginProvider::register($app, $config);
```

### 3. 配置环境变量（推荐）

在项目根目录创建 `.env` 文件：

```env
# AI 配置
AI_DEEPSEEK_API_KEY=your_api_key
AI_DEEPSEEK_MODEL=deepseek-chat
AI_DEEPSEEK_ENDPOINT=https://api.deepseek.com/chat/completions
AI_DEEPSEEK_TIMEOUT=30

# 其他模型配置...
```

### 4. 加载环境变量（如果需要）

如果 Slim 项目未自动加载 `.env` 文件，可使用 vlucas/phpdotenv 库：

```bash
composer require vlucas/phpdotenv
```

然后在应用初始化时加载：

```php
use Dotenv\Dotenv;

// 加载环境变量
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
```

## 使用示例

### 1. 基本调用

```php
// 基本调用
$app->get('/chat', function ($request, $response, $args) {
    $ai = $this->get('ai');
    $result = $ai->chat('什么是PHP？');
    $response->getBody()->write($result['text']);
    return $response;
});
```

### 2. 带参数调用

```php
// 带参数调用
$app->post('/chat', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $prompt = $data['prompt'] ?? '你好';
    
    $ai = $this->get('ai');
    $result = $ai->chat($prompt, [
        'temperature' => 0.8, // 温度参数，控制生成文本的随机性
        'max_tokens' => 512, // 最大生成 tokens 数
        'system' => '你是一位诗人，擅长写抒情诗', // 系统提示，定义 AI 角色
    ]);
    
    return $response->withJson(['result' => $result['text']]);
});
```

### 3. 流式响应

```php
// 流式响应
$app->get('/stream', function ($request, $response, $args) {
    $ai = $this->get('ai');
    $prompt = $request->getQueryParam('prompt', '写一篇关于 Slim 的文章');
    
    // 设置响应头
    $response = $response
        ->withHeader('Content-Type', 'text/event-stream')
        ->withHeader('Cache-Control', 'no-cache')
        ->withHeader('Connection', 'keep-alive');
    
    // 获取响应体
    $body = $response->getBody();
    
    // 流式调用
    $ai->streamChat($prompt, function ($chunk) use ($body) {
        $body->write($chunk);
        $body->flush();
    });
    
    return $response;
});
```

### 4. 自定义回调

```php
// 自定义回调
$app->get('/custom', function ($request, $response, $args) {
    $ai = $this->get('ai');
    $prompt = $request->getQueryParam('prompt', '如何在 Slim 中实现缓存？');
    
    // 自定义请求数据准备
    $ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
        return [
            'model' => $config['model'],
            'messages' => [
                ['role' => 'system', 'content' => '你是一位专业的 Slim 开发者'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ];
    });
    
    // 自定义响应解析
    $ai->setParseResponseCallback(function ($response, $config) {
        return [
            'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
            'model' => $response['model'] ?? 'unknown',
            'processed_at' => date('Y-m-d H:i:s'),
        ];
    });
    
    // 调用
    $result = $ai->chat($prompt);
    return $response->withJson($result);
});
```

### 5. 错误处理

```php
// 错误处理
$app->get('/error', function ($request, $response, $args) {
    try {
        $ai = $this->get('ai');
        $result = $ai->chat('你好');
        return $response->withJson(['result' => $result['text']]);
    } catch (\Exception $e) {
        // 记录错误日志
        $logger = $this->get('logger');
        $logger->error('AI 调用失败', ['error' => $e->getMessage()]);
        
        return $response->withJson(['error' => $e->getMessage()])->withStatus(500);
    }
});
```

## 完整应用示例

### 示例应用

```php
<?php

use Slim\Factory\AppFactory;
use Phpais\AiPlugin\Slim\AiPluginProvider;
use Slim\Middleware\ErrorMiddleware;

require __DIR__ . '/vendor/autoload.php';

// 加载环境变量
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/.env');
    foreach ($dotenv as $key => $value) {
        putenv(sprintf('%s=%s', $key, $value));
    }
}

// 创建 Slim 应用
$app = AppFactory::create();

// 添加错误中间件
$app->addErrorMiddleware(true, true, true);

// 配置 AI 插件
$config = [
    'default' => 'deepseek',
    'providers' => [
        'deepseek' => [
            'api_key' => getenv('AI_DEEPSEEK_API_KEY') ?: 'your_api_key',
            'model' => getenv('AI_DEEPSEEK_MODEL') ?: 'deepseek-chat',
            'endpoint' => getenv('AI_DEEPSEEK_ENDPOINT') ?: 'https://api.deepseek.com/chat/completions',
            'timeout' => (int)(getenv('AI_DEEPSEEK_TIMEOUT') ?: 30),
            'provider' => 'deepseek',
        ],
        'wenxin' => [
            'api_key' => getenv('AI_WENXIN_API_KEY') ?: '',
            'model' => getenv('AI_WENXIN_MODEL') ?: 'ernie-bot',
            'endpoint' => getenv('AI_WENXIN_ENDPOINT') ?: 'https://qianfan.baidubce.com/v2/chat/completions',
            'timeout' => (int)(getenv('AI_WENXIN_TIMEOUT') ?: 30),
            'provider' => 'wenxin',
        ],
    ],
];

// 注册 AI 插件
AiPluginProvider::register($app, $config);

// 路由定义

// 首页
$app->get('/', function ($request, $response, $args) {
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>PHP AI 插件 - Slim 示例</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h1>PHP AI 插件 - Slim 示例</h1>
            <div class="mt-4">
                <h2>功能演示</h2>
                <ul class="list-group">
                    <li class="list-group-item"><a href="/chat">基本聊天</a></li>
                    <li class="list-group-item"><a href="/chat-form">高级聊天</a></li>
                    <li class="list-group-item"><a href="/stream">流式响应</a></li>
                    <li class="list-group-item"><a href="/custom">自定义回调</a></li>
                </ul>
            </div>
        </div>
    </body>
    </html>
    HTML;
    
    $response->getBody()->write($html);
    return $response;
});

// 基本聊天
$app->get('/chat', function ($request, $response, $args) {
    $ai = $this->get('ai');
    $result = $ai->chat('你好，能介绍一下你自己吗？');
    
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>基本聊天 - PHP AI 插件</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h1>基本聊天示例</h1>
            <div class="mt-4 p-4 bg-light rounded">
                <h3>AI 回复：</h3>
                <p>{$result['text']}</p>
            </div>
            <a href="/" class="btn btn-primary mt-4">返回首页</a>
        </div>
    </body>
    </html>
    HTML;
    
    $response->getBody()->write($html);
    return $response;
});

// 高级聊天表单
$app->get('/chat-form', function ($request, $response, $args) {
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>高级聊天 - PHP AI 插件</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h1>高级聊天示例</h1>
            <form method="post" action="/chat-process">
                <div class="mb-3">
                    <label for="prompt" class="form-label">问题</label>
                    <textarea class="form-control" id="prompt" name="prompt" rows="3">写一首关于冬天的诗</textarea>
                </div>
                <div class="mb-3">
                    <label for="temperature" class="form-label">温度参数</label>
                    <input type="number" class="form-control" id="temperature" name="temperature" value="0.7" step="0.1" min="0" max="1">
                </div>
                <button type="submit" class="btn btn-primary">发送</button>
            </form>
            <a href="/" class="btn btn-secondary mt-4">返回首页</a>
        </div>
    </body>
    </html>
    HTML;
    
    $response->getBody()->write($html);
    return $response;
});

// 处理聊天请求
$app->post('/chat-process', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $prompt = $data['prompt'] ?? '你好';
    $temperature = (float)($data['temperature'] ?? 0.7);
    
    $ai = $this->get('ai');
    $result = $ai->chat($prompt, [
        'temperature' => $temperature,
        'max_tokens' => 500,
        'system' => '你是一位诗人，擅长写抒情诗'
    ]);
    
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>聊天结果 - PHP AI 插件</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h1>聊天结果</h1>
            <div class="mt-4 p-4 bg-light rounded">
                <h3>问题：</h3>
                <p>{$prompt}</p>
                <h3>AI 回复：</h3>
                <p>{$result['text']}</p>
            </div>
            <a href="/chat-form" class="btn btn-primary mt-4">重新发送</a>
            <a href="/" class="btn btn-secondary mt-4">返回首页</a>
        </div>
    </body>
    </html>
    HTML;
    
    $response->getBody()->write($html);
    return $response;
});

// 流式响应
$app->get('/stream', function ($request, $response, $args) {
    $prompt = $request->getQueryParam('prompt', '写一篇关于 Slim 框架的文章');
    
    $response = $response
        ->withHeader('Content-Type', 'text/event-stream')
        ->withHeader('Cache-Control', 'no-cache')
        ->withHeader('Connection', 'keep-alive');
    
    $body = $response->getBody();
    
    $ai = $this->get('ai');
    $ai->streamChat($prompt, function ($chunk) use ($body) {
        $body->write($chunk);
        $body->flush();
    });
    
    return $response;
});

// 自定义回调
$app->get('/custom', function ($request, $response, $args) {
    $ai = $this->get('ai');
    
    // 自定义请求数据
    $ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
        return [
            'model' => $config['model'],
            'messages' => [
                ['role' => 'system', 'content' => '你是一位简洁的助手，只提供简短的回答'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.5,
            'max_tokens' => 100,
        ];
    });
    
    // 自定义响应解析
    $ai->setParseResponseCallback(function ($response, $config) {
        return [
            'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
            'model' => $response['model'] ?? 'unknown',
            'processed_at' => date('Y-m-d H:i:s'),
        ];
    });
    
    $result = $ai->chat('如何快速学习 Slim 框架？');
    
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>自定义回调 - PHP AI 插件</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h1>自定义回调示例</h1>
            <div class="mt-4 p-4 bg-light rounded">
                <h3>AI 回复：</h3>
                <p>{$result['answer']}</p>
                <p class="text-muted">模型：{$result['model']}</p>
                <p class="text-muted">处理时间：{$result['processed_at']}</p>
            </div>
            <a href="/" class="btn btn-primary mt-4">返回首页</a>
        </div>
    </body>
    </html>
    HTML;
    
    $response->getBody()->write($html);
    return $response;
});

// 运行应用
$app->run();
```

## 常见问题

### 1. 服务未注册

**问题**：`$this->get('ai')` 抛出异常

**解决方案**：确保已正确注册 AI 服务，并且配置数组格式正确

### 2. API 密钥未生效

**问题**：API 调用失败，提示密钥错误

**解决方案**：检查环境变量是否正确设置，或直接在配置数组中硬编码 API 密钥进行测试

### 3. 流式响应不工作

**问题**：流式响应未实时输出

**解决方案**：确保服务器支持流式输出，并且在回调函数中使用 `$body->flush()` 刷新输出缓冲区

### 4. 超时问题

**问题**：请求超时

**解决方案**：在配置数组中增加超时时间：

```php
$config = [
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

**解决方案**：修改配置数组中的 `default` 值：

```php
$config = [
    'default' => 'wenxin', // 切换到文心模型
    // 其他配置...
];
```

## 高级用法

### 手动创建客户端

```php
// 手动创建不同模型的客户端
$factory = $app->getContainer()->get('ai.factory');

// 创建文心客户端
$wenxinConfig = $app->getContainer()->get('ai.config')['providers']['wenxin'];
$wenxinClient = $factory->create('wenxin', $wenxinConfig);
$result1 = $wenxinClient->chat('你好');

// 创建千问客户端
$qianwenConfig = $app->getContainer()->get('ai.config')['providers']['qianwen'];
$qianwenClient = $factory->create('qianwen', $qianwenConfig);
$result2 = $qianwenClient->chat('你好');
```

### 使用中间件

```php
// 创建 AI 中间件
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    // 可以在这里添加 AI 相关的逻辑
    return $response;
});
```

### 缓存 AI 响应

```php
// 使用缓存中间件
$app->get('/cached-chat', function ($request, $response, $args) {
    $cacheKey = 'ai_response_' . md5('你好');
    
    // 检查缓存
    if ($this->has('cache') && $this->get('cache')->has($cacheKey)) {
        $result = $this->get('cache')->get($cacheKey);
    } else {
        // 调用 AI
        $ai = $this->get('ai');
        $result = $ai->chat('你好');
        
        // 缓存结果
        if ($this->has('cache')) {
            $this->get('cache')->set($cacheKey, $result, 3600); // 缓存 1 小时
        }
    }
    
    return $response->withJson($result);
});
```