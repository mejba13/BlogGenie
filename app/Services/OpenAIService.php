<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
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

    /**
     * Generate blog post data from OpenAI API
     */
    public function generatePostData($title, $motto = '')
    {
        try {
            // Request content generation from OpenAI API
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
                    'max_tokens'  => 200, // Increased token limit for more detailed posts
                    'temperature' => 0.7,
                ],
                'timeout' => 600,
            ]);

            // Parse the response
            $body = json_decode($response->getBody()->getContents(), true);
            Log::info("OpenAI API response: " . json_encode($body));

            if (!isset($body['choices'][0]['message']['content'])) {
                Log::error("OpenAI API did not return content. Response: " . json_encode($body));
                throw new Exception("Failed to generate content.");
            }

            $content = $body['choices'][0]['message']['content'];
            $postData = $this->parseResponse($content, $title);

            // Generate a featured image based on post content
            $postData['featured_image_url'] = $this->generateImage($postData['content']);
            // Generate a video URL based on post content
            $postData['video_url'] = $this->generateVideo($postData['content']);

            return $postData;

        } catch (Exception $e) {
            Log::error("OpenAI API error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse OpenAI response to extract post data like content, categories, tags, and slug
     */
    private function parseResponse($response, $title)
    {
        $data = [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => '',
            'categories' => [],
            'tags' => [],
        ];

        $lines = explode("\n", $response);
        $isContent = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'Slug:') !== false) {
                $data['slug'] = Str::slug(trim(str_replace('Slug:', '', $line)));
            } elseif (strpos($line, 'Categories:') !== false) {
                $categories = str_replace('Categories:', '', $line);
                // Remove asterisks (*) and trim spaces
                $data['categories'] = array_map(function ($category) {
                    return trim(str_replace('*', '', $category));
                }, explode(',', $categories));
            } elseif (strpos($line, 'Tags:') !== false) {
                $tags = str_replace('Tags:', '', $line);
                // Remove asterisks (*) and trim spaces
                $data['tags'] = array_map(function ($tag) {
                    return trim(str_replace('*', '', $tag));
                }, explode(',', $tags));
            } elseif (strpos($line, 'Post Content:') !== false) {
                $isContent = true;
                continue;
            }

            if ($isContent && !empty($line)) {
                $data['content'] .= $line . "\n";
            }
        }

        if (empty(trim($data['content']))) {
            Log::error("Parsed content is empty. Response: " . $response);
            throw new Exception("Failed to generate post content.");
        }

        Log::info("Parsed data: " . json_encode($data));
        return $data;
    }

    /**
     * Generate a featured image based on the post content.
     */
    public function generateImage($postContent)
    {
        try {
            // Prompt for image generation based on post content
            $prompt = "Create a professional and visually appealing blog post featured image based on the following content: '$postContent'. The image should be clean, modern, and aesthetically pleasing. Avoid using any text on the image.";

            // Request image generation from OpenAI API
            $response = $this->client->post('https://api.openai.com/v1/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'prompt'       => $prompt,
                    'n'            => 1,
                    'size'         => '1024x1024',
                ],
                'timeout' => 300,
            ]);

            // Process the image generation response
            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['data'][0]['url'])) {
                $imageUrl = $body['data'][0]['url'];
                $imageContents = file_get_contents($imageUrl);

                $imageName = 'featured_images/' . Str::random(10) . '.jpg';
                if (!File::exists(public_path('featured_images'))) {
                    File::makeDirectory(public_path('featured_images'), 0755, true);
                }
                File::put(public_path($imageName), $imageContents);

                return $imageName;

            } else {
                throw new Exception('Image URL not found in OpenAI response.');
            }

        } catch (Exception $e) {
            Log::error("Failed to generate image: " . $e->getMessage());
            return $this->generatePlaceholderImage();
        }
    }

    /**
     * Generate a placeholder image if OpenAI image generation fails.
     */
    private function generatePlaceholderImage()
    {
        return 'https://via.placeholder.com/1024x1024.png?text=Placeholder';
    }

    /**
     * Generate a fallback video URL or embed link based on the post content.
     */
    public function generateVideo($content)
    {
        try {
            $response = $this->client->post('https://api.synthesia.io/v1/generate-video', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SYNTHESIA_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'script' => $content,
                    'voice'  => 'en-US',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            return $body['video_url'] ?? null;

        } catch (Exception $e) {
            Log::error("Failed to generate the video: " . $e->getMessage());
            return 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=819'; // Fallback video
        }
    }
}
