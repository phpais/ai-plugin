<?php

namespace Phpais\AiPlugin\Clients;

use Phpais\AiPlugin\Abstracts\AiClient;

class QianwenClient extends AiClient
{
    protected function getEndpoint(): string
    {
        return $this->config['endpoint'] ?? 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
    }
    
    protected function doPrepareRequestData(string $prompt, array $options): array
    {
        $data = [
            'model' => $this->config['model'] ?? 'ep-20240101123456-abcde',
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1024,
        ];
        
        // 如果提供了完整的消息数组，直接使用它
        if (isset($options['messages']) && is_array($options['messages'])) {
            $data['messages'] = $options['messages'];
        } else {
            // 否则使用传统的提示词格式
            $data['messages'] = [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];
            
            // 添加系统提示词
            if (isset($options['system'])) {
                array_unshift($data['messages'], [
                    'role' => 'system',
                    'content' => $options['system']
                ]);
            }
        }
        
        return $data;
    }
    
    protected function doParseResponse($response): array
    {
        return [
            'text' => $response['choices'][0]['message']['content'] ?? '',
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
