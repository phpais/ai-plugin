# Symfony 框架使用说明

## 安装配置

### 1. 注册 Bundle

在 `config/bundles.php` 文件中添加：

```php
return [
    // 其他 Bundle
    Phpais\AiPlugin\Symfony\AiPluginBundle::class => ['all' => true],
];
```

### 2. 配置

在 `config/packages/ai.yaml` 文件中配置：

```yaml
ai:
    default: deepseek
    providers:
        deepseek:
            api_key: '%env(AI_DEEPSEEK_API_KEY)%'
            model: '%env(AI_DEEPSEEK_MODEL)%'
            endpoint: '%env(AI_DEEPSEEK_ENDPOINT)%'
            timeout: '%env(int:AI_DEEPSEEK_TIMEOUT)%'
            provider: deepseek
        # 其他模型配置...
        wenxin:
            api_key: '%env(AI_WENXIN_API_KEY)%'
            model: '%env(AI_WENXIN_MODEL)%'
            endpoint: '%env(AI_WENXIN_ENDPOINT)%'
            timeout: '%env(int:AI_WENXIN_TIMEOUT)%'
            provider: wenxin
```

### 3. 配置环境变量

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

### 4. 清除缓存

运行以下命令清除缓存：

```bash
php bin/console cache:clear
```

## 使用示例

### 1. 基本调用

```php
// 在控制器中使用
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatController extends AbstractController
{
    public function index(ContainerInterface $container)
    {
        $ai = $container->get('ai');
        $result = $ai->chat('什么是PHP？');
        return $this->render('index.html.twig', ['result' => $result['text']]);
    }
}
```

### 2. 带参数调用

```php
// 带参数调用
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatController extends AbstractController
{
    public function chatAction(ContainerInterface $container)
    {
        $ai = $container->get('ai');
        $result = $ai->chat('写一首关于春天的诗', [
            'temperature' => 0.8, // 温度参数，控制生成文本的随机性
            'max_tokens' => 512, // 最大生成 tokens 数
            'system' => '你是一位诗人，擅长写抒情诗', // 系统提示，定义 AI 角色
        ]);
        return $this->render('chat.html.twig', ['result' => $result['text']]);
    }
}
```

### 3. 流式响应

```php
// 流式响应
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends AbstractController
{
    public function streamAction(ContainerInterface $container)
    {
        $ai = $container->get('ai');
        
        // 设置响应头
        $response = new StreamedResponse();
        $response->setCallback(function () use ($ai) {
            $ai->streamChat('写一篇关于 Symfony 的文章', function ($chunk) {
                echo $chunk;
                flush(); // 刷新输出缓冲区
            });
        });
        
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        
        return $response;
    }
}
```

### 4. 自定义回调

```php
// 自定义回调
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatController extends AbstractController
{
    public function customAction(ContainerInterface $container)
    {
        $ai = $container->get('ai');
        
        // 自定义请求数据准备
        $ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
            return [
                'model' => $config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => '你是一位专业的 Symfony 开发者'],
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
        $result = $ai->chat('如何在 Symfony 中实现缓存？');
        return $this->render('custom.html.twig', [
            'answer' => $result['answer'],
            'processed_at' => $result['processed_at']
        ]);
    }
}
```

### 5. 错误处理

```php
// 错误处理
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

class ChatController extends AbstractController
{
    public function errorAction(ContainerInterface $container, LoggerInterface $logger)
    {
        try {
            $ai = $container->get('ai');
            $result = $ai->chat('你好');
            return $this->render('error.html.twig', ['result' => $result['text']]);
        } catch (\Exception $e) {
            // 记录错误日志
            $logger->error('AI 调用失败', ['error' => $e->getMessage()]);
            
            return $this->render('error.html.twig', ['error' => $e->getMessage()]);
        }
    }
}
```

## 服务注入示例

### 使用构造函数注入

```php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Phpais\AiPlugin\Abstracts\AiClient;

class ChatController extends AbstractController
{
    private $ai;
    
    public function __construct(AiClient $ai)
    {
        $this->ai = $ai;
    }
    
    public function index()
    {
        $result = $this->ai->chat('你好');
        return new Response($result['text']);
    }
}
```

### 使用属性注入（Symfony 5.4+）

```php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Phpais\AiPlugin\Abstracts\AiClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ChatController extends AbstractController
{
    #[Autowire]
    private AiClient $ai;
    
    public function index()
    {
        $result = $this->ai->chat('你好');
        return new Response($result['text']);
    }
}
```

## 常见问题

### 1. 服务未找到

**问题**：`The service "ai" has not been registered.`

**解决方案**：确保已正确注册 Bundle 并清除缓存

### 2. 配置错误

**问题**：`Environment variable not found: AI_DEEPSEEK_API_KEY.`

**解决方案**：确保 `.env` 文件中已配置必要的环境变量

### 3. 流式响应不工作

**问题**：流式响应未实时输出

**解决方案**：确保服务器支持流式输出，并且在回调函数中使用 `flush()` 刷新输出缓冲区

### 4. 超时问题

**问题**：请求超时

**解决方案**：在配置文件中增加超时时间：

```yaml
# config/packages/ai.yaml
ai:
    providers:
        deepseek:
            # 其他配置...
            timeout: 60 # 增加超时时间
```

### 5. 模型切换

**问题**：如何切换不同的 AI 模型

**解决方案**：修改 `config/packages/ai.yaml` 文件中的 `default` 值：

```yaml
ai:
    default: wenxin # 切换到文心模型
    # 其他配置...
```

## 高级用法

### 手动创建客户端

```php
use Phpais\AiPlugin\Factories\AiClientFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChatController extends AbstractController
{
    public function switchModel(ContainerInterface $container)
    {
        $factory = $container->get('ai.factory');
        
        // 创建文心客户端
        $wenxinConfig = $container->getParameter('ai.config')['providers']['wenxin'];
        $wenxinClient = $factory->create('wenxin', $wenxinConfig);
        $result1 = $wenxinClient->chat('你好');
        
        // 创建千问客户端
        $qianwenConfig = $container->getParameter('ai.config')['providers']['qianwen'];
        $qianwenClient = $factory->create('qianwen', $qianwenConfig);
        $result2 = $qianwenClient->chat('你好');
        
        return $this->render('switch.html.twig', [
            'wenxin_result' => $result1['text'],
            'qianwen_result' => $result2['text']
        ]);
    }
}
```

### 使用 Twig 模板

在 Twig 模板中使用：

```twig
{# templates/chat.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
    <h1>AI 聊天</h1>
    <div class="result">
        {{ result }}
    </div>
{% endblock %}
```