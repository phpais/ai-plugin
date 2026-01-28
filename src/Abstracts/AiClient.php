<?php

namespace Phpais\AiPlugin\Abstracts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Phpais\AiPlugin\Contracts\AiClientInterface;

abstract class AiClient implements AiClientInterface
{
    protected $client;
    protected $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    
    abstract protected function getEndpoint(): string;
    
    abstract protected function prepareRequestData(string $prompt, array $options): array;
    
    abstract protected function parseResponse($response): array;
    
    protected function request(array $data, string $method = 'POST')
    {
        try {
            $response = $this->client->request($method, $this->getEndpoint(), [
                'json' => $data,
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw new \Exception('Client error: ' . $e->getMessage());
        } catch (ServerException $e) {
            throw new \Exception('Server error: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Request error: ' . $e->getMessage());
        }
    }
    
    protected function streamRequest(array $data, callable $callback)
    {
        $options = [
            'json' => $data,
            'stream' => true,
        ];
        
        try {
            $response = $this->client->request('POST', $this->getEndpoint(), $options);
            $body = $response->getBody();
            
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                if (!empty($chunk)) {
                    $callback($chunk);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Stream request error: ' . $e->getMessage());
        }
    }
    
    public function chat(string $prompt, array $options = []): array
    {
        $data = $this->prepareRequestData($prompt, $options);
        $response = $this->request($data);
        return $this->parseResponse($response);
    }
    
    public function streamChat(string $prompt, callable $callback, array $options = []): void
    {
        $data = $this->prepareRequestData($prompt, $options);
        $this->streamRequest($data, $callback);
    }
    
    public function generateImage(string $prompt, array $options = []): array
    {
        throw new \Exception('Image generation not supported by this model');
    }
    
    public function getModelInfo(): array
    {
        return [
            'name' => $this->config['model'] ?? 'unknown',
            'provider' => $this->config['provider'] ?? 'unknown',
        ];
    }
}
