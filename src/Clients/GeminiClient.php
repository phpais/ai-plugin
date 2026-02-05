<?php

namespace Phpais\AiPlugin\Clients;

use Phpais\AiPlugin\Abstracts\AiClient;

class GeminiClient extends AiClient
{
    protected function getEndpoint(): string
    {
        return $this->config['endpoint'] ?? 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent';
    }
    
    protected function doPrepareRequestData(string $prompt, array $options): array
    {
        $data = [
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 1024,
            ],
        ];
        
        // 如果提供了完整的消息数组，直接使用它
        if (isset($options['messages']) && is_array($options['messages'])) {
            // 转换消息格式以适应Gemini API
            $geminiContents = [];
            foreach ($options['messages'] as $message) {
                if ($message['role'] === 'system') {
                    // 系统消息使用systemInstruction
                    $data['systemInstruction'] = $message['content'];
                } else {
                    // 其他消息转换为Gemini格式
                    $geminiContents[] = [
                        'role' => $message['role'],
                        'parts' => [
                            [
                                'text' => $message['content']
                            ]
                        ]
                    ];
                }
            }
            $data['contents'] = $geminiContents;
        } else {
            // 否则使用传统的提示词格式
            $data['contents'] = [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ];
            
            // 添加系统提示词
            if (isset($options['system'])) {
                $data['systemInstruction'] = $options['system'];
            }
        }
        
        return $data;
    }
    
    protected function doParseResponse($response): array
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
