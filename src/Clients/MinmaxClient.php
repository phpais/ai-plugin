<?php

namespace Phpais\AiPlugin\Clients;

use Phpais\AiPlugin\Abstracts\AiClient;

class MinmaxClient extends AiClient
{
    protected function getEndpoint(): string
    {
        return $this->config['endpoint'] ?? 'https://api.minimax.chat/v1/text/chatcompletion_pro';
    }
    
    protected function prepareRequestData(string $prompt, array $options): array
    {
        $data = [
            'model' => $this->config['model'] ?? 'abab5.5-chat',
            'messages' => [
                [
                    'sender_type' => 'USER',
                    'text' => $prompt
                ]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1024,
        ];
        
        if (isset($options['system'])) {
            $data['system_prompt'] = $options['system'];
        }
        
        return $data;
    }
    
    protected function parseResponse($response): array
    {
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
