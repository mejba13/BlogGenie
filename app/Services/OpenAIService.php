<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class OpenAIService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('OPENAI_API_KEY');
    }

    public function generatePostData($title)
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
                                'content' => 'You are a helpful assistant that generates detailed blog posts. Ensure the content is at least 1200 characters long and provide relevant categories and tags.'
                            ],
                            [
                                'role'    => 'user',
                                'content' => "Generate a detailed blog post for the title: '$title'. Include a slug, post content, categories, and tags."
                            ],
                        ],
                        'max_tokens'  => 1500,
                        'temperature' => 0.7,
                    ],
                ]);

                $body = json_decode($response->getBody()->getContents(), true);

                if (!isset($body['choices'][0]['message']['content'])) {
                    throw new Exception("Failed to generate content.");
                }

                $content = $body['choices'][0]['message']['content'];
                return $this->parseResponse($content, $title);

            } catch (Exception $e) {
                $retryCount++;
                Log::error("OpenAI API error: " . $e->getMessage());

                if ($retryCount >= $maxRetries) {
                    throw $e;
                }

                sleep($delayBetweenRetries);
            }
        } while ($retryCount < $maxRetries);
    }

    private function parseResponse($response, $title)
    {
        $lines = explode("\n", $response);
        $data = [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => '',
            'categories' => [],
            'tags' => [],
        ];

        foreach ($lines as $line) {
            if (strpos($line, 'Slug:') !== false) {
                $data['slug'] = trim(str_replace('Slug:', '', $line));
            } elseif (strpos($line, 'Content:') !== false) {
                $data['content'] = trim(str_replace('Content:', '', $line));
            } elseif (strpos($line, 'Categories:') !== false) {
                $data['categories'] = array_map('trim', explode(',', str_replace('Categories:', '', $line)));
            } elseif (strpos($line, 'Tags:') !== false) {
                $data['tags'] = array_map('trim', explode(',', str_replace('Tags:', '', $line)));
            }
        }

        return $data;
    }
}
