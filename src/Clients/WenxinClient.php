<?php

namespace Phpais\AiPlugin\Clients;

use Phpais\AiPlugin\Abstracts\AiClient;

class WenxinClient extends AiClient
{
    protected function getEndpoint(): string
    {
        return $this->config['endpoint'] ?? 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions';
    }
    
    protected function prepareRequestData(string $prompt, array $options): array
    {
        $data = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'model' => $this->config['model'] ?? 'ernie-bot',
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1024,
        ];
        
        if (isset($options['system'])) {
            array_unshift($data['messages'], [
                'role' => 'system',
                'content' => $options['system']
            ]);
        }
        
        return $data;
    }
    
    protected function parseResponse($response): array
    {
        return [
            'text' => $response['result'] ?? '',
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
