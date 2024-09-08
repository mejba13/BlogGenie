<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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
                'timeout' => 120, // Set the timeout to 30 seconds
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
        try {
            $response = $this->client->post('https://api.openai.com/v1/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'prompt'       => $content,
                    'n'            => 1,
                    'size'         => '1024x1024',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['data'][0]['url'])) {
                $imageUrl = $body['data'][0]['url'];

                // Get the image content from the URL
                $imageContents = file_get_contents($imageUrl);

                // Define the path in the storage/app/public folder
                $imageName = 'featured_images/' . Str::random(10) . '.jpg';
                Storage::disk('public')->put($imageName, $imageContents);

                return $imageName; // Return the relative path of the saved image

            } else {
                throw new Exception('Image URL not found in OpenAI response.');
            }

        } catch (Exception $e) {
            Log::error("Failed to generate or save the image: " . $e->getMessage());

            // If image generation fails, return a placeholder image
            return $this->generatePlaceholderImage($content);
        }
    }

    private function generatePlaceholderImage($content)
    {
        // Extract a summary or keyword from the content for the placeholder text
        $text = Str::limit(strip_tags($content), 30);

        // Create a placeholder image with text using Intervention Image
        $image = \Intervention\Image\Facades\Image::canvas(1024, 1024, '#cccccc'); // Grey background

        // Add text to the placeholder image
        $image->text($text, 512, 512, function ($font) {
            $font->file(public_path('fonts/Roboto-Bold.ttf')); // Ensure this file is present
            $font->size(40);
            $font->color('#000000');
            $font->align('center');
            $font->valign('middle');
        });

        // Define the file name and path for the placeholder image
        $imageName = 'featured_images/' . Str::random(10) . '_placeholder.jpg';

        // Save the placeholder image in storage/app/public/featured_images
        Storage::disk('public')->put($imageName, (string) $image->encode('jpg'));

        // Return the path where the image can be accessed (via /storage/featured_images/your_image.jpg)
        return $imageName;
    }

    private function generateVideo($content)
    {
        try {
            // Generate a video based on content using a third-party service like Synthesia or any other API
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

            // If video generation fails, return a fallback video (e.g., a YouTube link)
            return 'https://www.youtube.com/embed/dQw4w9WgXcQ?start=819'; // Fallback video
        }
    }
}
