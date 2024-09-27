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

    public function generatePostData($title, $motto = '')
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
                            'content' => 'You are a helpful assistant that generates well-structured and SEO-optimized blog posts. The content should be in HTML format with clear headings, paragraphs, and include a Table of Contents at the start of the post. The Table of Contents should have links to each section.'
                        ],
                        [
                            'role'    => 'user',
                            'content' => "Generate a detailed blog post for the title: '$title'. Include a slug, post content, categories, tags, and a table of contents. Make sure the content is structured in HTML and easy to read."
                        ],
                    ],
                    'max_tokens'  => 2000,
                    'temperature' => 0.7,
                ],
                'timeout' => 600,  // Increased timeout for large requests
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (!isset($body['choices'][0]['message']['content'])) {
                throw new Exception("OpenAI API did not return valid content: " . json_encode($body));
            }

            $content = $body['choices'][0]['message']['content'];
            $postData = $this->parseResponseWithTOC($content, $title);

            // Generate featured image with title and motto
            $postData['featured_image_url'] = $this->generateImage($postData['title'], $motto);
            $postData['video_url'] = $this->generateVideo($postData['content']);

            return $postData;

        } catch (Exception $e) {
            Log::error("OpenAI API error: " . $e->getMessage());
            throw $e;  // Propagate the exception to the caller
        }
    }

    private function parseResponseWithTOC($response, $title)
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
        $toc = '<h2>Table of Contents</h2><ul>'; // For the table of contents
        $content = '';
        $sectionNumber = 1;

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'Slug:') !== false) {
                $data['slug'] = Str::slug(trim(str_replace('Slug:', '', $line)));
            } elseif (strpos($line, 'Categories:') !== false) {
                $categories = str_replace('Categories:', '', $line);
                $data['categories'] = array_map(function ($category) {
                    return str_replace('*', '', trim($category));
                }, explode(',', $categories));
            } elseif (strpos($line, 'Tags:') !== false) {
                $tags = str_replace('Tags:', '', $line);
                $data['tags'] = array_map(function ($tag) {
                    return str_replace('*', '', trim($tag));
                }, explode(',', $tags));
            } elseif (strpos($line, 'Post Content:') !== false) {
                $isContent = true;
                continue;
            }

            if ($isContent && !empty($line)) {
                if (strpos($line, '## ') === 0) {
                    $toc .= "<li><a href='#section-$sectionNumber'>" . substr($line, 3) . "</a></li>";
                    $content .= "<h2 id='section-$sectionNumber'>" . substr($line, 3) . "</h2>";
                    $sectionNumber++;
                } else {
                    $content .= "<p>$line</p>";
                }
            }
        }

        $toc .= '</ul>';

        if (empty(trim($content))) {
            Log::error("Parsed content is empty. Response: " . $response);
            throw new Exception("Failed to generate post content.");
        }

        $data['content'] = $toc . $content;
        Log::info("Parsed data with TOC: " . json_encode($data));

        return $data;
    }

    public function generateImage($title, $motto)
    {
        try {
            $prompt = "Create a high-quality, professional blog post featured image with the text: '$title'. The image should be clean, modern, and aesthetically pleasing. Use a balanced layout with readable fonts. Incorporate a subtle background.";

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

                // Add title motto text to image
                $this->addTextToImage(public_path($imageName), $title, $motto);
                return $imageName;

            } else {
                throw new Exception('Image URL not found in OpenAI response.');
            }

        } catch (Exception $e) {
            Log::error("Failed to generate image: " . $e->getMessage());
            return $this->generatePlaceholderImage($title);
        }
    }

    private function addTextToImage($imagePath, $title, $motto)
    {
        try {
            $image = new \Imagick($imagePath);
            $draw = new \ImagickDraw();

            // Set text color to black for better contrast
            $draw->setFillColor(new \ImagickPixel('black'));

            // Define the font path
            $fontPath = public_path('fonts/Roboto-Bold.ttf');
            if (!file_exists($fontPath)) {
                throw new Exception("Font not found at $fontPath");
            }
            $draw->setFont($fontPath);

            // Set font size for the title
            $draw->setFontSize(60);  // Increase font size for better readability

            // Set Gravity to top center for the title
            $draw->setGravity(\Imagick::GRAVITY_NORTH);

            // Draw the title in the top center of the image
            $image->annotateImage($draw, 0, 70, 0, Str::limit(strip_tags($title), 50));

            // Change font size for the motto and set gravity to bottom center
            $draw->setFontSize(40);  // Increase font size for motto
            $draw->setGravity(\Imagick::GRAVITY_SOUTH);

            // Draw the motto at the bottom of the image
            $image->annotateImage($draw, 0, 50, 0, Str::limit(strip_tags($motto), 100));

            // Write the image to disk
            $image->writeImage($imagePath);

            // Clear Imagick object resources
            $image->clear();
            $image->destroy();

        } catch (Exception $e) {
            Log::error("Failed to add text to image: " . $e->getMessage());
        }
    }

    private function generatePlaceholderImage($title)
    {
        $text = Str::limit(strip_tags($title), 30);
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
