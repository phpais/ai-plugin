<?php

namespace Phpais\AiPlugin\Clients;

use Phpais\AiPlugin\Abstracts\AiClient;

class MinmaxClient extends AiClient
{
    protected function getEndpoint(): string
    {
        return $this->config['endpoint'] ?? 'https://api.minimaxi.com/v1/text/chatcompletion_v2';
    }
    
    protected function doPrepareRequestData(string $prompt, array $options): array
    {
        $data = [
            'model' => $this->config['model'] ?? 'abab5.5-chat',
        ];
        
        // 添加可选参数（根据文档要求）
        if (isset($options['temperature'])) {
            $data['temperature'] = $options['temperature'];
        }
        if (isset($options['max_tokens'])) {
            $data['max_completion_tokens'] = $options['max_tokens'];
        }
        
        // 如果提供了完整的消息数组，处理它
        if (isset($options['messages']) && is_array($options['messages'])) {
            $processedMessages = [];
            foreach ($options['messages'] as $message) {
                // 确保消息包含必要的字段
                $processedMessage = [
                    'role' => $message['role'],
                    'content' => $message['content']
                ];
                
                // 添加name字段（如果不存在）
                if (!isset($message['name'])) {
                    if ($message['role'] === 'system') {
                        $processedMessage['name'] = 'MiniMax AI';
                    } elseif ($message['role'] === 'user') {
                        $processedMessage['name'] = '用户';
                    } elseif ($message['role'] === 'assistant') {
                        $processedMessage['name'] = 'MiniMax AI';
                    }
                } else {
                    $processedMessage['name'] = $message['name'];
                }
                
                $processedMessages[] = $processedMessage;
            }
            $data['messages'] = $processedMessages;
        } else {
            // 否则使用传统的提示词格式
            $data['messages'] = [
                [
                    'role' => 'user',
                    'content' => $prompt,
                    'name' => '用户'
                ]
            ];
            
            // 添加系统提示词
            if (isset($options['system'])) {
                array_unshift($data['messages'], [
                    'role' => 'system',
                    'content' => $options['system'],
                    'name' => 'MiniMax AI'
                ]);
            }
        }
        
        return $data;
    }
    
    protected function doParseResponse($response): array
    {
        // 检查是否有错误
        if (isset($response['base_resp']) && $response['base_resp']['status_code'] !== 0) {
            return [
                'text' => "API错误: " . $response['base_resp']['status_msg'],
                'usage' => $response['usage'] ?? [],
                'model' => $response['model'] ?? $this->config['model'],
                'id' => $response['id'] ?? '',
                'error' => [
                    'code' => $response['base_resp']['status_code'],
                    'message' => $response['base_resp']['status_msg']
                ]
            ];
        }
        
        // 检查新的API端点响应格式
        if (isset($response['choices']) && isset($response['choices'][0]['message']['content'])) {
            return [
                'text' => $response['choices'][0]['message']['content'] ?? '',
                'usage' => $response['usage'] ?? [],
                'model' => $response['model'] ?? $this->config['model'],
                'id' => $response['id'] ?? '',
            ];
        }
        
        // 保持旧的响应格式兼容
        return [
            'text' => $response['reply'] ?? '',
            'usage' => $response['usage'] ?? [],
            'model' => $response['model'] ?? $this->config['model'],
            'id' => $response['id'] ?? '',
        ];
    }
    
    public function __construct(array $config)
    {
        parent::__construct($config);
        
        // 添加认证头
        $this->client = new \GuzzleHttp\Client([
            'timeout' => $config['timeout'] ?? 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $config['api_key'],
            ],
        ]);
    }
}
