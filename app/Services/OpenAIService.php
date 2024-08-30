<?php

namespace App\Services;

use GuzzleHttp\Client;

class OpenAIService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('OPENAI_API_KEY');
        set_time_limit(60); // Extend the execution time to 60 seconds
    }

    public function generateBlogPost($prompt)
    {
        $retryCount = 0;
        $maxRetries = 3;
        $delayBetweenRetries = 60; // in seconds

        do {
            try {
                $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'model'       => 'gpt-3.5-turbo',
                        'messages'    => [
                            [
                                'role'    => 'system',
                                'content' => 'You are a helpful assistant.',
                            ],
                            [
                                'role'    => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'max_tokens'  => 1000,
                        'temperature' => 0.7,
                    ],
                ]);

                $body = json_decode($response->getBody()->getContents(), true);
                return $body['choices'][0]['message']['content'];

            } catch (\Exception $e) {
                $retryCount++;

                // Log the error for debugging
                Log::error("OpenAI API error: " . $e->getMessage());

                if ($retryCount >= $maxRetries) {
                    throw $e; // Rethrow the exception if max retries reached
                }

                // Wait before retrying
                sleep($delayBetweenRetries);
            }
        } while ($retryCount < $maxRetries);
    }
}
