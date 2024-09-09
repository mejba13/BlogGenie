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
                'timeout' => 300, // Set the timeout to 30 seconds
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

                $imageContents = file_get_contents($imageUrl);

                $imageName = 'featured_images/' . Str::random(10) . '.jpg';

                if (!File::exists(public_path('featured_images'))) {
                    File::makeDirectory(public_path('featured_images'), 0755, true);
                }

                file_put_contents(public_path($imageName), $imageContents);

                return $imageName;


            } else {
                throw new Exception('Image URL not found in OpenAI response.');
            }

        } catch (Exception $e) {
            Log::error("Failed to generate or save the image: " . $e->getMessage());

            // If image generation fails, return a placeholder image
            return $this->generatePlaceholderImage($content);
        }
    }

//    private function generatePlaceholderImage($content)
//    {
//        // Extract a summary or keyword from the content for the placeholder text
//        $text = Str::limit(strip_tags($content), 30);
//
//        // Create a placeholder image with text using Intervention Image
//        $image = \Intervention\Image\Facades\Image::canvas(1024, 1024, '#cccccc'); // Grey background
//
//        // Add text to the placeholder image
//        $image->text($text, 512, 512, function ($font) {
//            $font->file(public_path('fonts/Roboto-Bold.ttf')); // Ensure this file is present
//            $font->size(40);
//            $font->color('#000000');
//            $font->align('center');
//            $font->valign('middle');
//        });
//
//        $imageName = 'featured_images/' . Str::random(10) . '_placeholder.jpg';
//        if (!File::exists(public_path('featured_images'))) {
//            File::makeDirectory(public_path('featured_images'), 0755, true);
//        }
//        $image->save(public_path($imageName));
//        return $imageName;
//    }

    private function generatePlaceholderImage($content)
    {
        // Extract a summary or keyword from the content for the placeholder text
        $text = Str::limit(strip_tags($content), 50);  // Increase text limit for better appearance

        // Create an empty 1024x1024 image with a white background
        $width = 1024;
        $height = 1024;
        $image = imagecreatetruecolor($width, $height);

        // Set background color to white
        $backgroundColor = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

        // Load a background image (optional)
        $bgImagePath = public_path('images/background.jpg'); // Ensure you have a background image here
        if (file_exists($bgImagePath)) {
            $background = imagecreatefromjpeg($bgImagePath);
            imagecopyresized($image, $background, 0, 0, 0, 0, $width, $height, imagesx($background), imagesy($background));
            imagedestroy($background);
        }

        // Set text color to black
        $textColor = imagecolorallocate($image, 0, 0, 0);

        // Define the font size and path
        $fontPath = public_path('fonts/Roboto-Bold.ttf'); // Ensure this file is present
        $fontSize = 40;

        // Add a title to the image (larger font size for emphasis)
        $title = 'Generated Image';
        $titleSize = 60;
        $bbox = imagettfbbox($titleSize, 0, $fontPath, $title);
        $titleWidth = $bbox[2] - $bbox[0];
        $titleX = ($width - $titleWidth) / 2;
        $titleY = ($height / 4);  // Place title in the upper quarter
        imagettftext($image, $titleSize, 0, $titleX, $titleY, $textColor, $fontPath, $title);

        // Add text to the image (centered)
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $textX = ($width - $textWidth) / 2;
        $textY = ($height / 2) + ($fontSize / 2);
        imagettftext($image, $fontSize, 0, $textX, $textY, $textColor, $fontPath, $text);

        // Optionally, add a border around the image
        $borderColor = imagecolorallocate($image, 0, 0, 0);
        imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);  // Adds a black border

        // Define the file name and path for the placeholder image
        $imageName = 'featured_images/' . Str::random(10) . '_placeholder.jpg';
        $imagePath = public_path($imageName);

        // Ensure the directory exists
        if (!File::exists(public_path('featured_images'))) {
            File::makeDirectory(public_path('featured_images'), 0755, true);
        }

        // Save the image as a JPEG file
        imagejpeg($image, $imagePath);

        // Destroy the image resource to free memory
        imagedestroy($image);

        return $imageName; // Return the relative path of the saved image
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
