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
                            'content' => 'You are a helpful assistant that generates detailed blog posts.'
                        ],
                        [
                            'role'    => 'user',
                            'content' => "Generate a detailed blog post for the title: '$title'. Include a slug, post content, categories, tags, a featured image, and a video URL."
                        ],
                    ],
                    'max_tokens'  => 200,
                    'temperature' => 0.7,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // Log the full response to debug any issues
            Log::info("OpenAI API response: " . json_encode($body));

            if (!isset($body['choices'][0]['message']['content'])) {
                Log::error("OpenAI API did not return content. Response: " . json_encode($body));
                throw new Exception("Failed to generate content.");
            }

            // Parse the response content
            $content = $body['choices'][0]['message']['content'];
            $postData = $this->parseResponse($content, $title);

            // Generate featured image and video
            $postData['featured_image_url'] = $this->generateImage($postData['content']);
            $postData['video_url'] = $this->generateVideo($postData['content']);

            return $postData;

        } catch (Exception $e) {
            Log::error("OpenAI API error: " . $e->getMessage());
            throw $e;
        }
    }

    private function parseResponse($response, $title)
    {
        $data = [
            'title' => $title,
            'slug' => Str::slug($title), // Default slug based on title
            'content' => '',
            'categories' => [],
            'tags' => [],
        ];

        $lines = explode("\n", $response);
        $isContent = false;

        foreach ($lines as $line) {
            $line = trim($line); // Remove unnecessary whitespace and special characters

            if (strpos($line, 'Slug:') !== false) {
                $data['slug'] = Str::slug(trim(str_replace('Slug:', '', $line)));
            } elseif (strpos($line, 'Categories:') !== false) {
                $categories = str_replace(['Categories:', '****'], '', $line);
                $data['categories'] = array_map('trim', explode(',', $categories));
            } elseif (strpos($line, 'Tags:') !== false) {
                $tags = str_replace(['Tags:', '****'], '', $line);
                $data['tags'] = array_map('trim', explode(',', $tags));
            } elseif (strpos($line, 'Post Content:') !== false) {
                $isContent = true;
                continue;
            }

            // Collect content after 'Post Content:' marker
            if ($isContent && !empty($line)) {
                $data['content'] .= $line . "\n";
            }
        }

        // Ensure content is not empty and correctly populated
        if (empty(trim($data['content']))) {
            Log::error("Parsed content is empty. Response: " . $response);
            throw new Exception("Failed to generate post content.");
        }

        // Log the parsed data for debugging purposes
        Log::info("Parsed data: " . json_encode($data));

        return $data;
    }

    private function generateImage($content)
    {
        // Here, implement the logic to generate an image based on the content
        // For now, we will simulate this with a placeholder image URL.
        $imageUrl = 'https://via.placeholder.com/800x400?text=' . urlencode(Str::limit($content, 50));
        return $imageUrl;
    }

    private function generateVideo($content)
    {
        // Here, implement the logic to generate a video based on the content
        // For now, we will simulate this with a YouTube video URL.
        $videoUrl = 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=' . rand(0, 1000);
        return $videoUrl;
    }
}
