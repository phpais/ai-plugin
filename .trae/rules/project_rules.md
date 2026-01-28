# PHP AI 插件项目规则

## 项目概述

PHP AI 插件是一个功能强大的AI接口整合工具，旨在帮助PHP开发者快速对接多种AI模型接口，支持ThinkPHP和Laravel框架。

### 核心功能

- ✅ 支持多个AI模型：文心、千问、火山、DeepSeek
- ✅ 统一API接口，简化AI调用
- ✅ 支持流式响应
- ✅ 支持ThinkPHP 6.0+
- ✅ 支持Laravel 8.0+
- ✅ 灵活的配置系统

## 项目结构

```
phpais/
├── src/
│   ├── Abstracts/         # 抽象类
│   │   └── AiClient.php    # AI客户端抽象基类
│   ├── Clients/            # 具体AI模型客户端
│   │   ├── WenxinClient.php    # 文心AI客户端
│   │   ├── QianwenClient.php   # 千问AI客户端
│   │   ├── VolcanoClient.php   # 火山AI客户端
│   │   └── DeepseekClient.php  # DeepSeek AI客户端
│   ├── Config/             # 配置文件
│   │   └── ai.php          # AI配置文件
│   ├── Contracts/          # 接口定义
│   │   └── AiClientInterface.php  # AI客户端接口
│   ├── Factories/          # 工厂类
│   │   └── AiClientFactory.php    # AI客户端工厂
│   ├── Laravel/            # Laravel适配
│   │   ├── AiPluginServiceProvider.php  # Laravel服务提供者
│   │   └── Facades/        # Laravel门面
│   │       └── AI.php      # AI门面类
│   └── ThinkPHP/           # ThinkPHP适配
│       └── Service.php     # ThinkPHP服务
├── examples/               # 示例代码
│   └── basic_usage.php     # 基本使用示例
├── composer.json           # Composer配置
└── README.md               # 项目文档
```

## 核心API

### AiClientInterface

```php
interface AiClientInterface
{
    public function chat(string $prompt, array $options = []): array;
    public function streamChat(string $prompt, callable $callback, array $options = []): void;
    public function generateImage(string $prompt, array $options = []): array;
    public function getModelInfo(): array;
}
```

### 主要方法

1. **chat()**: 发送文本请求到AI模型，返回完整响应
2. **streamChat()**: 发送流式文本请求，实时返回响应片段
3. **generateImage()**: 生成图像（部分模型支持）
4. **getModelInfo()**: 获取模型信息

## 配置系统

### 环境变量配置

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
```

### 配置文件结构

配置文件位于 `src/Config/ai.php`，包含默认模型设置和各模型的详细配置。

## 使用方法

### Laravel框架

```php
use Phpais\AiPlugin\Laravel\Facades\AI;

// 基本调用
$result = AI::chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 带参数调用
$result = AI::chat('写一首关于春天的诗', [
    'temperature' => 0.8,
    'max_tokens' => 512,
    'system' => '你是一位诗人，擅长写抒情诗'
]);

// 流式响应
AI::streamChat('写一篇关于AI的文章', function ($chunk) {
    echo $chunk;
    flush();
});
```

### ThinkPHP框架

```php
// 基本调用
$result = app('ai')->chat('你好，能介绍一下你自己吗？');
echo $result['text'];

// 带参数调用
$result = app('ai')->chat('写一首关于春天的诗', [
    'temperature' => 0.8,
    'max_tokens' => 512,
    'system' => '你是一位诗人，擅长写抒情诗'
]);

// 流式响应
app('ai')->streamChat('写一篇关于AI的文章', function ($chunk) {
    echo $chunk;
    flush();
});
```

### 直接使用工厂类

```php
use Phpais\AiPlugin\Factories\AiClientFactory;

// 创建特定模型客户端
$wenxinClient = AiClientFactory::create('wenxin', $config);
$result = $wenxinClient->chat('你好');

$deepseekClient = AiClientFactory::create('deepseek', $config);
$result = $deepseekClient->chat('你好');
```

## 常见问题

### 1. API密钥配置

**问题**: 如何配置API密钥？
**解决方案**: 在 `.env` 文件中设置对应的API密钥，例如 `AI_WENXIN_API_KEY=your_api_key`。

### 2. 模型切换

**问题**: 如何切换不同的AI模型？
**解决方案**: 修改 `.env` 文件中的 `AI_DEFAULT` 设置，或直接使用工厂类创建特定模型的客户端。

### 3. 流式响应

**问题**: 流式响应不工作？
**解决方案**: 确保服务器支持流式输出，并且在回调函数中使用 `flush()` 函数刷新输出缓冲区。

### 4. 超时设置

**问题**: 请求超时？
**解决方案**: 在配置文件中增加超时时间，例如 `'timeout' => 60`。

### 5. 框架兼容

**问题**: 如何在不同框架中使用？
**解决方案**: 
- Laravel: 使用 `AI::chat()` 静态方法
- ThinkPHP: 使用 `app('ai')->chat()` 方法

## 代码规范

### 命名约定

- 类名: 采用 PascalCase 命名法
- 方法名: 采用 camelCase 命名法
- 变量名: 采用 camelCase 命名法
- 常量名: 采用 SNAKE_CASE 命名法

### 代码结构

- 每个类文件应只包含一个类
- 类应遵循单一职责原则
- 方法应简洁明了，避免过长的方法体
- 适当使用注释说明复杂逻辑

## 扩展指南

### 添加新AI模型

1. 创建新的客户端类，继承自 `AiClient` 抽象类
2. 实现必要的方法：`getEndpoint()`、`prepareRequestData()`、`parseResponse()`
3. 在配置文件中添加新模型的配置项
4. 在工厂类中注册新模型

### 框架适配

1. 为新框架创建服务提供者或服务类
2. 实现框架特定的注册和引导逻辑
3. 提供框架特定的配置加载机制

## 部署建议

1. **环境变量管理**: 使用 `.env` 文件管理敏感配置，避免硬编码API密钥
2. **错误处理**: 实现适当的错误处理机制，捕获和记录API调用错误
3. **性能优化**: 对于频繁调用的场景，考虑实现缓存机制
4. **监控日志**: 记录AI调用的日志，便于调试和分析

## 技术支持

- **文档**: 参考项目的 README.md 文件
- **示例**: 查看 examples 目录下的示例代码
- **源码**: 阅读源码了解详细实现

## 版本管理

- 遵循语义化版本规范
- 主版本号: 不兼容的API变更
- 次版本号: 向下兼容的功能添加
- 修订号: 向下兼容的问题修复

## 许可证

项目采用 MIT 许可证，详见 LICENSE 文件。
