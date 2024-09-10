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
                'timeout' => 300,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // Log the full response for debugging
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
                $data['categories'] = array_map('trim', explode(',', $categories));
            } elseif (strpos($line, 'Tags:') !== false) {
                $tags = str_replace('Tags:', '', $line);
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

        if (empty(trim($data['content']))) {
            Log::error("Parsed content is empty. Response: " . $response);
            throw new Exception("Failed to generate post content.");
        }

        Log::info("Parsed data: " . json_encode($data));
        return $data;
    }

    public function generateImage($content)
    {
        try {
            $prompt = Str::limit(strip_tags($content), 1000);

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

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['data'][0]['url'])) {
                $imageUrl = $body['data'][0]['url'];
                $imageContents = file_get_contents($imageUrl);

                $imageName = 'featured_images/' . Str::random(10) . '.jpg';
                if (!File::exists(public_path('featured_images'))) {
                    File::makeDirectory(public_path('featured_images'), 0755, true);
                }
                File::put(public_path($imageName), $imageContents);

                $this->addTextToImage(public_path($imageName), $content);
                return $imageName;

            } else {
                throw new Exception('Image URL not found in OpenAI response.');
            }

        } catch (Exception $e) {
            Log::error("Failed to generate image: " . $e->getMessage());
            return $this->generatePlaceholderImage($content);
        }
    }

    private function addTextToImage($imagePath, $content)
    {
        try {
            $image = new \Imagick($imagePath);
            $draw = new \ImagickDraw();
            $draw->setFillColor('black');
            $draw->setFont(public_path('fonts/Roboto-Bold.ttf'));
            $draw->setFontSize(40);
            $draw->setGravity(\Imagick::GRAVITY_CENTER);

            $text = Str::limit(strip_tags($content), 50);

            $image->annotateImage($draw, 0, 0, 0, $text);
            $image->writeImage($imagePath);

            $image->clear();
            $image->destroy();
        } catch (Exception $e) {
            Log::error("Failed to add text to image: " . $e->getMessage());
        }
    }

    private function generatePlaceholderImage($content)
    {
        $text = Str::limit(strip_tags($content), 30);
        return 'https://via.placeholder.com/1024x1024.png?text=' . urlencode($text);
    }

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
            return 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=819';
        }
    }
}
