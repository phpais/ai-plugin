<?php

namespace Phpais\AiPlugin\Contracts;

interface AiClientInterface
{
    /**
     * 发送文本请求到AI模型
     * 
     * @param string $prompt 提示词
     * @param array $options 可选参数
     * @return array 响应结果
     */
    public function chat(string $prompt, array $options = []): array;
    
    /**
     * 发送流式文本请求到AI模型
     * 
     * @param string $prompt 提示词
     * @param callable $callback 回调函数，用于处理流式响应
     * @param array $options 可选参数
     * @return void
     */
    public function streamChat(string $prompt, callable $callback, array $options = []): void;
    
    /**
     * 生成图像
     * 
     * @param string $prompt 提示词
     * @param array $options 可选参数
     * @return array 响应结果
     */
    public function generateImage(string $prompt, array $options = []): array;
    
    /**
     * 获取模型信息
     * 
     * @return array 模型信息
     */
    public function getModelInfo(): array;
}
