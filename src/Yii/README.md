# Yii 框架使用说明

## 安装配置

### 1. 注册模块

在 `config/web.php` 文件中添加：

```php
return [
    'modules' => [
        'ai' => [
            'class' => 'Phpais\AiPlugin\Yii\AiPluginModule',
        ],
    ],
    'components' => [
        // 其他组件...
    ],
    'params' => [
        'ai' => [
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
        ],
    ],
];
```

### 2. 配置环境变量（推荐）

在项目根目录创建 `.env` 文件：

```env
# AI 配置
AI_DEEPSEEK_API_KEY=your_deepseek_api_key
AI_DEEPSEEK_MODEL=deepseek-chat
AI_DEEPSEEK_ENDPOINT=https://api.deepseek.com/chat/completions
AI_DEEPSEEK_TIMEOUT=30

# 其他模型配置...
```

### 3. 加载环境变量（如果需要）

如果 Yii 项目未自动加载 `.env` 文件，可在 `index.php` 中添加：

```php
// index.php
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/.env');
    foreach ($dotenv as $key => $value) {
        putenv(sprintf('%s=%s', $key, $value));
    }
}
```

## 使用示例

### 1. 基本调用

```php
// 在控制器中使用
namespace app\controllers;

use yii\web\Controller;

class ChatController extends Controller
{
    public function actionIndex()
    {
        $ai = \Yii::$app->get('ai');
        $result = $ai->chat('什么是PHP？');
        return $this->render('index', ['result' => $result['text']]);
    }
}
```

### 2. 带参数调用

```php
// 带参数调用
namespace app\controllers;

use yii\web\Controller;

class ChatController extends Controller
{
    public function actionChat()
    {
        $ai = \Yii::$app->get('ai');
        $result = $ai->chat('写一首关于春天的诗', [
            'temperature' => 0.8, // 温度参数，控制生成文本的随机性
            'max_tokens' => 512, // 最大生成 tokens 数
            'system' => '你是一位诗人，擅长写抒情诗', // 系统提示，定义 AI 角色
        ]);
        return $this->render('chat', ['result' => $result['text']]);
    }
}
```

### 3. 流式响应

```php
// 流式响应
namespace app\controllers;

use yii\web\Controller;

class ChatController extends Controller
{
    public function actionStream()
    {
        $ai = \Yii::$app->get('ai');
        
        // 禁用输出缓冲
        ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        $ai->streamChat('写一篇关于 Yii 的文章', function ($chunk) {
            echo $chunk;
            flush();
            ob_flush();
        });
        
        return '';
    }
}
```

### 4. 自定义回调

```php
// 自定义回调
namespace app\controllers;

use yii\web\Controller;

class ChatController extends Controller
{
    public function actionCustom()
    {
        $ai = \Yii::$app->get('ai');
        
        // 自定义请求数据准备
        $ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
            return [
                'model' => $config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => '你是一位专业的 Yii 开发者'],
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
        $result = $ai->chat('如何在 Yii 中实现缓存？');
        return $this->render('custom', [
            'answer' => $result['answer'],
            'processed_at' => $result['processed_at']
        ]);
    }
}
```

### 5. 错误处理

```php
// 错误处理
namespace app\controllers;

use yii\web\Controller;

class ChatController extends Controller
{
    public function actionError()
    {
        try {
            $ai = \Yii::$app->get('ai');
            $result = $ai->chat('你好');
            return $this->render('error', ['result' => $result['text']]);
        } catch (\Exception $e) {
            // 记录错误日志
            \Yii::error('AI 调用失败: ' . $e->getMessage(), __METHOD__);
            
            return $this->render('error', ['error' => $e->getMessage()]);
        }
    }
}
```

## 完整控制器示例

### 示例控制器

```php
<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Request;

class AIController extends Controller
{
    /**
     * @var Request
     */
    private $request;
    
    public function __construct($id, $module, Request $request, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->request = $request;
    }
    
    /**
     * 基本聊天
     */
    public function actionIndex()
    {
        $prompt = $this->request->get('prompt', '你好');
        $ai = \Yii::$app->get('ai');
        $result = $ai->chat($prompt);
        
        return $this->render('index', [
            'prompt' => $prompt,
            'result' => $result['text']
        ]);
    }
    
    /**
     * 带参数聊天
     */
    public function actionAdvanced()
    {
        $prompt = $this->request->post('prompt', '写一首关于秋天的诗');
        $temperature = (float)$this->request->post('temperature', 0.7);
        
        $ai = \Yii::$app->get('ai');
        $result = $ai->chat($prompt, [
            'temperature' => $temperature,
            'max_tokens' => 500,
            'system' => '你是一位诗人，擅长写抒情诗'
        ]);
        
        return $this->render('advanced', [
            'prompt' => $prompt,
            'temperature' => $temperature,
            'result' => $result['text']
        ]);
    }
    
    /**
     * 流式响应
     */
    public function actionStream()
    {
        $prompt = $this->request->get('prompt', '写一篇关于 Yii 框架的文章');
        
        // 禁用输出缓冲
        ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        $ai = \Yii::$app->get('ai');
        $ai->streamChat($prompt, function ($chunk) {
            echo $chunk;
            flush();
            ob_flush();
        });
        
        return '';
    }
    
    /**
     * 自定义回调
     */
    public function actionCustom()
    {
        $prompt = $this->request->get('prompt', '如何优化 Yii 应用性能？');
        
        $ai = \Yii::$app->get('ai');
        
        // 自定义请求数据
        $ai->setPrepareRequestDataCallback(function ($prompt, $options, $config) {
            return [
                'model' => $config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => '你是一位专业的 Yii 开发者，提供详细的技术解答'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.6,
                'max_tokens' => 800,
            ];
        });
        
        // 自定义响应解析
        $ai->setParseResponseCallback(function ($response, $config) {
            return [
                'answer' => $response['choices'][0]['message']['content'] ?? '无响应',
                'model' => $response['model'] ?? 'unknown',
                'usage' => $response['usage'] ?? [],
                'timestamp' => time(),
            ];
        });
        
        $result = $ai->chat($prompt);
        
        return $this->render('custom', [
            'prompt' => $prompt,
            'result' => $result
        ]);
    }
}
```

## 常见问题

### 1. 服务未注册

**问题**：`\Yii::$app->get('ai')` 抛出异常

**解决方案**：确保已在 `config/web.php` 文件中正确注册 AI 模块和参数

### 2. API 密钥未生效

**问题**：API 调用失败，提示密钥错误

**解决方案**：检查环境变量是否正确设置，或直接在配置文件中硬编码 API 密钥进行测试

### 3. 流式响应不工作

**问题**：流式响应未实时输出

**解决方案**：确保服务器支持流式输出，并且在回调函数中使用 `flush()` 刷新输出缓冲区

### 4. 超时问题

**问题**：请求超时

**解决方案**：在配置文件中增加超时时间：

```php
// config/web.php
'params' => [
    'ai' => [
        'providers' => [
            'deepseek' => [
                // 其他配置...
                'timeout' => 60, // 增加超时时间
            ],
        ],
    ],
],
```

### 5. 模型切换

**问题**：如何切换不同的 AI 模型

**解决方案**：修改 `config/web.php` 文件中的 `default` 值：

```php
'params' => [
    'ai' => [
        'default' => 'wenxin', // 切换到文心模型
        // 其他配置...
    ],
],
```

## 高级用法

### 手动创建客户端

```php
use Phpais\AiPlugin\Factories\AiClientFactory;

// 创建不同模型的客户端
$factory = \Yii::$app->get('ai.factory');

// 创建文心客户端
$wenxinConfig = \Yii::$app->params['ai']['providers']['wenxin'];
$wenxinClient = $factory->create('wenxin', $wenxinConfig);
$result1 = $wenxinClient->chat('你好');

// 创建千问客户端
$qianwenConfig = \Yii::$app->params['ai']['providers']['qianwen'];
$qianwenClient = $factory->create('qianwen', $qianwenConfig);
$result2 = $qianwenClient->chat('你好');
```

### 使用依赖注入

在 Yii 2.0 中使用依赖注入：

```php
namespace app\controllers;

use yii\web\Controller;
use Phpais\AiPlugin\Abstracts\AiClient;

class ChatController extends Controller
{
    private $ai;
    
    public function __construct($id, $module, AiClient $ai, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->ai = $ai;
    }
    
    public function actionIndex()
    {
        $result = $this->ai->chat('你好');
        return $this->render('index', ['result' => $result['text']]);
    }
}
```

## 视图示例

### 基本视图

```php
// views/chat/index.php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<h1>AI 聊天</h1>

<?php $form = ActiveForm::begin(['method' => 'get']); ?>
    <div class="form-group">
        <?= $form->field($model, 'prompt')->textarea(['rows' => 3])->label('问题') ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton('发送', ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>

<?php if (isset($result)): ?>
    <div class="result mt-4 p-3 bg-light border rounded">
        <h3>AI 回复：</h3>
        <p><?= nl2br(Html::encode($result)) ?></p>
    </div>
<?php endif; ?>
```