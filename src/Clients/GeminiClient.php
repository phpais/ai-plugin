<?php

namespace Phpais\AiPlugin\Clients;

use Phpais\AiPlugin\Abstracts\AiClient;

class GeminiClient extends AiClient
{
    protected function getEndpoint(): string
    {
        return $this->config['endpoint'] ?? 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent';
    }
    
    protected function prepareRequestData(string $prompt, array $options): array
    {
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 1024,
            ],
        ];
        
        if (isset($options['system'])) {
            $data['systemInstruction'] = $options['system'];
        }
        
        return $data;
    }
    
    protected function parseResponse($response): array
    {
        return [
            'text' => $response['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'usage' => $response['usageMetadata'] ?? [],
            'model' => $this->config['model'] ?? 'gemini-pro',
            'id' => $response['requestId'] ?? '',
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
            ],
        ]);
    }
    
    protected function request(array $data, string $method = 'POST')
    {
        // 为Gemini API添加API密钥到URL
        $endpoint = $this->getEndpoint() . '?key=' . $this->config['api_key'];
        
        try {
            $response = $this->client->request($method, $endpoint, [
                'json' => $data,
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception('Gemini request error: ' . $e->getMessage());
        }
    }
}
