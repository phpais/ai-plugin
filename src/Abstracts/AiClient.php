<?php

namespace Phpais\AiPlugin\Abstracts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Phpais\AiPlugin\Contracts\AiClientInterface;

/**
 * AI客户端抽象基类
 * 
 * 实现了AiClientInterface接口的通用方法，提供了AI模型调用的基础框架
 * 子类需要实现具体的端点获取、请求数据准备和响应解析方法
 */
abstract class AiClient implements AiClientInterface
{
    /**
     * GuzzleHTTP客户端实例
     * 用于发送HTTP请求到AI模型API
     * 
     * @var Client
     */
    protected $client;
    
    /**
     * 客户端配置信息
     * 包含API密钥、模型名称、超时设置等配置
     * 
     * @var array
     */
    protected $config;
    
    /**
     * 自定义请求数据准备回调
     * 
     * @var callable|null
     */
    protected $prepareRequestDataCallback;
    
    /**
     * 自定义响应解析回调
     * 
     * @var callable|null
     */
    protected $parseResponseCallback;
    
    /**
     * 构造函数
     * 
     * @param array $config 客户端配置信息
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        
        // 初始化GuzzleHTTP客户端
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30, // 默认超时时间30秒
            'headers' => [
                'Content-Type' => 'application/json', // 默认Content-Type为application/json
            ],
        ]);
    }
    
    /**
     * 获取AI模型API端点
     * 
     * 子类必须实现此方法，返回具体AI模型的API端点URL
     * 
     * @return string AI模型API端点URL
     */
    abstract protected function getEndpoint(): string;
    

    
    /**
     * 发送HTTP请求
     * 
     * 发送HTTP请求到AI模型API，并处理响应和异常
     * 
     * @param array $data 请求数据
     * @param string $method HTTP方法，默认POST
     * @return array API响应数据
     * @throws \Exception 当请求失败时抛出异常
     */
    protected function request(array $data, string $method = 'POST')
    {
        try {
            // 发送HTTP请求
            $response = $this->client->request($method, $this->getEndpoint(), [
                'json' => $data, // 将数据以JSON格式发送
            ]);
            
            // 解析JSON响应
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            // 处理客户端错误（4xx错误）
            throw new \Exception('Client error: ' . $e->getMessage());
        } catch (ServerException $e) {
            // 处理服务器错误（5xx错误）
            throw new \Exception('Server error: ' . $e->getMessage());
        } catch (\Exception $e) {
            // 处理其他错误
            throw new \Exception('Request error: ' . $e->getMessage());
        }
    }
    
    /**
     * 发送流式HTTP请求
     * 
     * 发送流式HTTP请求到AI模型API，并通过回调函数处理流式响应
     * 
     * @param array $data 请求数据
     * @param callable $callback 回调函数，用于处理流式响应
     * @return void
     * @throws \Exception 当请求失败时抛出异常
     */
    protected function streamRequest(array $data, callable $callback)
    {
        // 流式请求选项
        $options = [
            'json' => $data, // 将数据以JSON格式发送
            'stream' => true, // 启用流式响应
        ];
        
        try {
            // 发送流式HTTP请求
            $response = $this->client->request('POST', $this->getEndpoint(), $options);
            $body = $response->getBody();
            
            // 读取流式响应数据
            while (!$body->eof()) {
                $chunk = $body->read(1024); // 每次读取1024字节
                if (!empty($chunk)) {
                    $callback($chunk); // 调用回调函数处理数据块
                }
            }
        } catch (\Exception $e) {
            // 处理错误
            throw new \Exception('Stream request error: ' . $e->getMessage());
        }
    }
    
    /**
     * 发送文本请求到AI模型
     * 
     * @param string $prompt 提示词
     * @param array $options 可选参数
     * @return array 响应结果
     */
    public function chat(string $prompt, array $options = []): array
    {
        // 准备请求数据
        $data = $this->prepareRequestData($prompt, $options);
        
        // 发送请求
        $response = $this->request($data);
        
        // 解析响应
        return $this->parseResponse($response);
    }
    
    /**
     * 发送流式文本请求到AI模型
     * 
     * @param string $prompt 提示词
     * @param callable $callback 回调函数，用于处理流式响应
     * @param array $options 可选参数
     * @return void
     */
    public function streamChat(string $prompt, callable $callback, array $options = []): void
    {
        // 准备请求数据
        $data = $this->prepareRequestData($prompt, $options);
        
        // 发送流式请求
        $this->streamRequest($data, $callback);
    }
    
    /**
     * 生成图像
     * 
     * 默认实现，抛出异常表示不支持图像生成
     * 子类可以重写此方法以支持图像生成
     * 
     * @param string $prompt 提示词
     * @param array $options 可选参数
     * @return array 响应结果
     * @throws \Exception 默认抛出不支持图像生成的异常
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        throw new \Exception('Image generation not supported by this model');
    }
    
    /**
     * 获取模型信息
     * 
     * @return array 模型信息
     */
    public function getModelInfo(): array
    {
        return [
            'name' => $this->config['model'] ?? 'unknown', // 模型名称
            'provider' => $this->config['provider'] ?? 'unknown', // 模型提供者
        ];
    }
    
    /**
     * 设置自定义请求数据准备回调
     * 
     * @param callable $callback 回调函数
     * @return $this
     */
    public function setPrepareRequestDataCallback(callable $callback)
    {
        $this->prepareRequestDataCallback = $callback;
        return $this;
    }
    
    /**
     * 设置自定义响应解析回调
     * 
     * @param callable $callback 回调函数
     * @return $this
     */
    public function setParseResponseCallback(callable $callback)
    {
        $this->parseResponseCallback = $callback;
        return $this;
    }
    
    /**
     * 准备请求数据
     * 
     * 检查是否有自定义回调，如果有则使用回调，否则调用子类实现的方法
     * 
     * @param string $prompt 提示词
     * @param array $options 可选参数
     * @return array 格式化的请求数据
     */
    protected function prepareRequestData(string $prompt, array $options): array
    {
        if (isset($this->prepareRequestDataCallback)) {
            return call_user_func($this->prepareRequestDataCallback, $prompt, $options, $this->config);
        }
        return $this->doPrepareRequestData($prompt, $options);
    }
    
    /**
     * 实际的请求数据准备方法
     * 
     * 子类必须实现此方法
     * 
     * @param string $prompt 提示词
     * @param array $options 可选参数
     * @return array 格式化的请求数据
     */
    abstract protected function doPrepareRequestData(string $prompt, array $options): array;
    
    /**
     * 解析响应数据
     * 
     * 检查是否有自定义回调，如果有则使用回调，否则调用子类实现的方法
     * 
     * @param mixed $response API响应数据
     * @return array 统一格式的响应数据
     */
    protected function parseResponse($response): array
    {
        if (isset($this->parseResponseCallback)) {
            return call_user_func($this->parseResponseCallback, $response, $this->config);
        }
        return $this->doParseResponse($response);
    }
    
    /**
     * 实际的响应解析方法
     * 
     * 子类必须实现此方法
     * 
     * @param mixed $response API响应数据
     * @return array 统一格式的响应数据
     */
    abstract protected function doParseResponse($response): array;
}
