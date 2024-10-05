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
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                Log::info("Starting OpenAI request for title: {$title}, attempt: " . ($attempts + 1));

                // Request to OpenAI API for generating the post content
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
                                'content' => 'You are a helpful assistant that generates well-structured, SEO-optimized blog posts. The content should be clean, without <html> or <body> tags, and organized using <h1>, <h2>, and <p> tags. Avoid generating text in images. Provide categories, tags, meta title, and meta description as plain text.'
                            ],
                            [
                                'role'    => 'user',
                                'content' => "Generate a detailed blog post for the title: '$title'. Structure the post with clear <h1>, <h2>, and <p> tags. Ensure the post has categories, tags, meta title, meta description, and post content."
                            ],
                        ],
                        'max_tokens'  => 1500, // Adjust token limit for larger responses
                        'temperature' => 0.7,
                    ],
                    'timeout' => 600,
                ]);

                $body = json_decode($response->getBody()->getContents(), true);

                // Log the response for debugging
                Log::info("OpenAI API response: " . json_encode($body));

                if (!isset($body['choices'][0]['message']['content']) || empty($body['choices'][0]['message']['content'])) {
                    throw new Exception("OpenAI API did not return valid content: " . json_encode($body));
                }

                // Sanitize and parse the content
                $content = $body['choices'][0]['message']['content'];
                $sanitizedContent = $this->sanitizeContent($content);
                $postData = $this->parseResponse($sanitizedContent, $title);

                // Generate the featured image based on post content
                $postData['featured_image_url'] = $this->generateImage($postData['title'], substr(strip_tags($postData['content']), 0, 150));

                // Fallback Video URL (YouTube Link)
                $postData['video_url'] = $this->generateVideo($postData['content']) ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=819';

                return $postData;

            } catch (Exception $e) {
                Log::error("Attempt " . ($attempts + 1) . " failed: " . $e->getMessage());
                $attempts++;

                if ($attempts >= $maxAttempts) {
                    throw new Exception("OpenAI API failed after multiple attempts.");
                }

                // Wait before retrying
                sleep(2);
            }
        }
    }

    /**
     * Sanitize the content to remove unwanted characters
     */
    private function sanitizeContent($content)
    {
        $sanitizedContent = preg_replace('/\x00/', '', $content);
        Log::info("Sanitized content: " . substr($sanitizedContent, 0, 200));  // Log a snippet of the sanitized content
        return $sanitizedContent;
    }

    /**
     * Parse OpenAI API response to extract post data, categories, tags, meta title, and meta description
     */
    public function parseResponse($response, $title)
    {
        $data = [
            'title'      => $title,
            'slug'       => Str::slug($title),
            'content'    => '',  // Initialize content
            'meta_title' => '',
            'meta_description' => '',
            'categories' => [],
            'tags'       => [],
        ];

        $lines = explode("\n", $response);
        $isContent = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, 'Slug:') !== false) {
                $data['slug'] = Str::slug(trim(str_replace('Slug:', '', $line)));
            } elseif (strpos($line, 'Meta Title:') !== false) {
                // Ensure plain text meta title
                $data['meta_title'] = strip_tags(trim(str_replace('Meta Title:', '', $line)));
            } elseif (strpos($line, 'Meta Description:') !== false) {
                // Ensure plain text meta description
                $data['meta_description'] = strip_tags(trim(str_replace('Meta Description:', '', $line)));
            } elseif (strpos($line, 'Categories:') !== false) {
                // Parse categories as plain text, separated by commas
                $categories = str_replace('Categories:', '', $line);
                $data['categories'] = array_map('trim', explode(',', $categories));
            } elseif (strpos($line, 'Tags:') !== false) {
                // Parse tags as plain text, separated by commas
                $tags = str_replace('Tags:', '', $line);
                $data['tags'] = array_map('trim', explode(',', $tags));
            } elseif (strpos($line, 'Post Content:') !== false) {
                $isContent = true;
                continue;
            }

            // Gather the post content under Post Content section
            if ($isContent) {
                $data['content'] .= "<p>" . htmlspecialchars($line) . "</p>";
            }
        }

        if (empty(trim($data['content']))) {
            Log::error("Parsed content is empty. Response: " . $response);
            throw new Exception("Failed to generate post content.");
        }

        Log::info("Parsed post data: " . json_encode($data));
        return $data;
    }

    /**
     * Generate a featured image for the post based on its content
     */
    public function generateImage($title, $contentSummary)
    {
        try {
            $prompt = "Create a high-quality, professional blog post featured image based on the following content: '$contentSummary'. The image should be clean, modern, and aesthetically pleasing. Avoid using text in the image.";

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
                'timeout' => 600,
            ]);

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
            return $this->generatePlaceholderImage($title);
        }
    }

    /**
     * Generate a placeholder image if OpenAI image generation fails
     */
    private function generatePlaceholderImage($title)
    {
        $text = Str::limit(strip_tags($title), 30);
        return 'https://via.placeholder.com/1024x1024.png?text=' . urlencode($text);
    }

    /**
     * Generate a fallback video link or embed
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
            return null;  // No video generated, return null
        }
    }
}
