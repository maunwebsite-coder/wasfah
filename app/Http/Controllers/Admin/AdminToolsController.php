<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Services\ImageCompressionService;
use App\Services\SimpleImageCompressionService;
use App\Support\Currency;
use App\Support\ImageUploadConstraints;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminToolsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tools = Tool::ordered()->paginate(15);
        return view('admin.tools.index', compact('tools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = [
            'Ø£Ø¬Ù‡Ø²Ø© Ø§Ù„Ø®Ø¨Ø²',
            'Ø£Ø¯ÙˆØ§Øª Ø§Ù„Ù‚ÙŠØ§Ø³',
            'Ù‚ÙˆØ§Ù„Ø¨ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª',
            'Ø£Ø¯ÙˆØ§Øª Ø§Ù„Ø®Ù„Ø·',
            'Ø£Ø¯ÙˆØ§Øª Ø§Ù„ØªØ²ÙŠÙŠÙ†',
            'Ø£Ø¯ÙˆØ§Øª Ø£Ø®Ø±Ù‰'
        ];
        
        return view('admin.tools.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate(
                [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'image' => array_merge(['nullable'], ImageUploadConstraints::rules()),
                    'amazon_url' => 'nullable|url',
                    'affiliate_url' => 'nullable|url',
                    'price' => 'required|numeric|min:0',
                    'category' => 'required|string|max:255',
                    'rating' => 'nullable|numeric|min:0|max:5',
                    'features' => 'nullable|array',
                    'features.*' => 'nullable|string|max:255',
                    'is_active' => 'boolean',
                    'sort_order' => 'nullable|integer|min:0'
                ],
                ImageUploadConstraints::messages('image', [
                    'ar' => 'ØµÙˆØ±Ø© Ø§Ù„Ø£Ø¯Ø§Ø©',
                    'en' => 'tool image',
                ])
            );

            $data = $request->all();

            // Handle image upload with compression
            if ($request->hasFile('image')) {
                // Ù…Ø­Ø§ÙˆÙ„Ø© Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø¶ØºØ· Ø§Ù„ØµÙˆØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù… Ø£ÙˆÙ„Ø§Ù‹
                if (extension_loaded('gd')) {
                    $data['image'] = ImageCompressionService::compressAndStore(
                        $request->file('image'),
                        'tools',
                        80, // Ø¬ÙˆØ¯Ø© 80%
                        1200, // Ø£Ù‚ØµÙ‰ Ø¹Ø±Ø¶
                        1200  // Ø£Ù‚ØµÙ‰ Ø§Ø±ØªÙØ§Ø¹
                    );
                } else {
                    // Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø§Ù„Ø­ÙØ¸ Ø§Ù„Ù…Ø¨Ø§Ø´Ø± Ø¥Ø°Ø§ Ù„Ù… ÙŠÙƒÙ† GD Ù…ØªÙˆÙØ±Ø§Ù‹
                    $data['image'] = SimpleImageCompressionService::compressAndStore(
                        $request->file('image'),
                        'tools',
                        80
                    );
                }
            } elseif (!empty($data['extracted_image_url'])) {
                // Download and save extracted image with compression
                $data['image'] = $this->downloadAndSaveImage($data['extracted_image_url']);
            }

            // Handle gallery images extracted from Amazon
            $galleryPaths = [];
            if (!empty($data['extracted_gallery_images'])) {
                $decodedGallery = json_decode($data['extracted_gallery_images'], true);
                if (is_array($decodedGallery) && !empty($decodedGallery)) {
                    $galleryPaths = $this->downloadAndSaveGalleryImages($decodedGallery);
                }
            }

            unset($data['extracted_image_url'], $data['extracted_gallery_images']);

            if (!empty($galleryPaths)) {
                if (empty($data['image'])) {
                    $data['image'] = $galleryPaths[0];
                }
                $data['gallery_images'] = $this->mergePrimaryIntoGallery($data['image'] ?? null, $galleryPaths);
            } elseif (!empty($data['image'])) {
                $data['gallery_images'] = [$data['image']];
            } else {
                $data['gallery_images'] = [];
            }

            // Clean and validate URLs
            if (!empty($data['amazon_url'])) {
                $data['amazon_url'] = trim($data['amazon_url']);
            }
            if (!empty($data['affiliate_url'])) {
                $data['affiliate_url'] = trim($data['affiliate_url']);
            }

            // Convert features array to JSON
            if ($request->has('features')) {
                $features = $request->input('features', []);
                // Filter out empty values and ensure all are strings
                $data['features'] = array_filter(array_map('trim', $features), function($feature) {
                    return !empty($feature) && is_string($feature);
                });
            } else {
                $data['features'] = [];
            }

            // Set default values
            $data['is_active'] = $request->boolean('is_active');
            $data['sort_order'] = $request->sort_order ?? 0;
            $data['rating'] = $request->rating ?? 0;

            // Debug: Log the data being saved
            \Log::info('Creating tool with data:', $data);

            $tool = Tool::create($data);

            \Log::info('Tool created successfully with ID:', ['id' => $tool->id]);

            return redirect()->route('admin.tools.index')
                ->with('success', 'ØªÙ… Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ø£Ø¯Ø§Ø© Ø¨Ù†Ø¬Ø§Ø­!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error:', $e->errors());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error creating tool:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø­ÙØ¸ Ø§Ù„Ø£Ø¯Ø§Ø©: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tool $tool): View
    {
        return view('admin.tools.show', compact('tool'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tool $tool): View
    {
        $categories = [
            'Ø£Ø¬Ù‡Ø²Ø© Ø§Ù„Ø®Ø¨Ø²',
            'Ø£Ø¯ÙˆØ§Øª Ø§Ù„Ù‚ÙŠØ§Ø³',
            'Ù‚ÙˆØ§Ù„Ø¨ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª',
            'Ø£Ø¯ÙˆØ§Øª Ø§Ù„Ø®Ù„Ø·',
            'Ø£Ø¯ÙˆØ§Øª Ø§Ù„ØªØ²ÙŠÙŠÙ†',
            'Ø£Ø¯ÙˆØ§Øª Ø£Ø®Ø±Ù‰'
        ];
        
        return view('admin.tools.edit', compact('tool', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tool $tool): RedirectResponse
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => array_merge(['nullable'], ImageUploadConstraints::rules()),
                'amazon_url' => 'nullable|url',
                'affiliate_url' => 'nullable|url',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:255',
                'rating' => 'nullable|numeric|min:0|max:5',
                'features' => 'nullable|array',
                'features.*' => 'nullable|string|max:255',
                'is_active' => 'boolean',
                'sort_order' => 'nullable|integer|min:0'
            ],
            ImageUploadConstraints::messages('image', [
                'ar' => 'ØµÙˆØ±Ø© Ø§Ù„Ø£Ø¯Ø§Ø©',
                'en' => 'tool image',
            ])
        );

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($tool->image && Storage::disk('public')->exists($tool->image)) {
                if (extension_loaded('gd')) {
                    ImageCompressionService::deleteCompressedImage($tool->image);
                } else {
                    SimpleImageCompressionService::deleteImage($tool->image);
                }
            }
            
            // Ù…Ø­Ø§ÙˆÙ„Ø© Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø¶ØºØ· Ø§Ù„ØµÙˆØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù… Ø£ÙˆÙ„Ø§Ù‹
            if (extension_loaded('gd')) {
                $data['image'] = ImageCompressionService::compressAndStore(
                    $request->file('image'),
                    'tools',
                    80, // Ø¬ÙˆØ¯Ø© 80%
                    1200, // Ø£Ù‚ØµÙ‰ Ø¹Ø±Ø¶
                    1200  // Ø£Ù‚ØµÙ‰ Ø§Ø±ØªÙØ§Ø¹
                );
            } else {
                // Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø§Ù„Ø­ÙØ¸ Ø§Ù„Ù…Ø¨Ø§Ø´Ø± Ø¥Ø°Ø§ Ù„Ù… ÙŠÙƒÙ† GD Ù…ØªÙˆÙØ±Ø§Ù‹
                $data['image'] = SimpleImageCompressionService::compressAndStore(
                    $request->file('image'),
                    'tools',
                    80
                );
            }
        } elseif (!empty($data['extracted_image_url'])) {
            // Delete old image
            if ($tool->image && Storage::disk('public')->exists($tool->image)) {
                Storage::disk('public')->delete($tool->image);
            }
            // Download and save extracted image
            $data['image'] = $this->downloadAndSaveImage($data['extracted_image_url']);
        }

        // Handle gallery images extracted from Amazon
        $galleryPaths = [];
        if (!empty($data['extracted_gallery_images'])) {
            $decodedGallery = json_decode($data['extracted_gallery_images'], true);
            if (is_array($decodedGallery) && !empty($decodedGallery)) {
                $galleryPaths = $this->downloadAndSaveGalleryImages($decodedGallery);
            }
        }

        unset($data['extracted_image_url'], $data['extracted_gallery_images']);

        if (!empty($galleryPaths)) {
            $this->deleteGalleryImages($tool->gallery_images ?? []);
            if (empty($data['image'])) {
                $data['image'] = $galleryPaths[0];
            }
            $data['gallery_images'] = $this->mergePrimaryIntoGallery($data['image'] ?? null, $galleryPaths);
        } else {
            $existingGallery = is_array($tool->gallery_images) ? $tool->gallery_images : [];

            // Remove old primary image if it has been replaced
            if (!empty($tool->image) && (!empty($data['image']) && $data['image'] !== $tool->image)) {
                $existingGallery = array_values(array_filter($existingGallery, function ($path) use ($tool) {
                    return $path !== $tool->image;
                }));
            }

            if (!empty($data['image'])) {
                $data['gallery_images'] = $this->mergePrimaryIntoGallery($data['image'], $existingGallery);
            } else {
                $data['gallery_images'] = $existingGallery;
            }
        }

        // Clean and validate URLs
        if (!empty($data['amazon_url'])) {
            $data['amazon_url'] = trim($data['amazon_url']);
        }
        if (!empty($data['affiliate_url'])) {
            $data['affiliate_url'] = trim($data['affiliate_url']);
        }

        // Convert features array to JSON
        if ($request->has('features')) {
            $features = $request->input('features', []);
            // Filter out empty values and ensure all are strings
            $data['features'] = array_filter(array_map('trim', $features), function($feature) {
                return !empty($feature) && is_string($feature);
            });
        } else {
            $data['features'] = [];
        }

        // Set default values
        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $request->sort_order ?? 0;
        $data['rating'] = $request->rating ?? 0;

        $tool->update($data);

        return redirect()->route('admin.tools.index')
            ->with('success', 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø£Ø¯Ø§Ø© Ø¨Ù†Ø¬Ø§Ø­!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tool $tool): RedirectResponse
    {
        $this->deleteGalleryImages($tool->gallery_images ?? []);

        // Delete image if exists
        if ($tool->image && Storage::disk('public')->exists($tool->image)) {
            Storage::disk('public')->delete($tool->image);
        }

        $tool->delete();

        return redirect()->route('admin.tools.index')
            ->with('success', 'ØªÙ… Ø­Ø°Ù Ø§Ù„Ø£Ø¯Ø§Ø© Ø¨Ù†Ø¬Ø§Ø­!');
    }

    /**
     * Toggle tool active status
     */
    public function toggle(Tool $tool): RedirectResponse
    {
        $tool->update(['is_active' => !$tool->is_active]);
        
        $status = $tool->is_active ? 'ØªÙØ¹ÙŠÙ„' : 'Ø¥Ù„ØºØ§Ø¡ ØªÙØ¹ÙŠÙ„';
        return redirect()->back()
            ->with('success', "ØªÙ… {$status} Ø§Ù„Ø£Ø¯Ø§Ø© Ø¨Ù†Ø¬Ø§Ø­!");
    }

    /**
     * Extract data from Amazon URL
     */
    public function extractAmazonData(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->url;
        
        try {
            // Handle shortened URLs first
            if (strpos($url, 'amzn.to') !== false || strpos($url, 'bit.ly') !== false) {
                $url = $this->resolveShortUrl($url);
                if (!$url) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ÙØ´Ù„ ÙÙŠ Ø­Ù„ Ø§Ù„Ø±Ø§Ø¨Ø· Ø§Ù„Ù…Ø®ØªØµØ±'
                    ]);
                }
            }
            
            // Use a simple web scraping approach
            $html = $this->fetchAmazonPage($url);
            
            if (!$html) {
                return response()->json([
                    'success' => false,
                    'message' => 'ÙØ´Ù„ ÙÙŠ ØªØ­Ù…ÙŠÙ„ ØµÙØ­Ø© Amazon'
                ]);
            }

            $data = $this->parseAmazonData($html);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Resolve shortened URLs to get the final Amazon URL
     */
    private function resolveShortUrl($shortUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $shortUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects automatically
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 300 && $httpCode < 400) {
            // Extract location header
            if (preg_match('/location:\s*(.+)/i', $response, $matches)) {
                $redirectUrl = trim($matches[1]);
                // If it's still a shortened URL, resolve it again
                if (strpos($redirectUrl, 'amzn.to') !== false || strpos($redirectUrl, 'bit.ly') !== false) {
                    return $this->resolveShortUrl($redirectUrl);
                }
                return $redirectUrl;
            }
        }
        
        return false;
    }

    /**
     * Fetch Amazon page content
     */
    private function fetchAmazonPage($url)
    {
        // Use cURL to fetch the page with better headers
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Handle gzip
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$html) {
            return false;
        }
        
        // Handle gzip decompression
        if (strpos($html, "\x1f\x8b") === 0) {
            $html = gzdecode($html);
        }
        
        // Check if we got a valid HTML response
        if (strlen($html) < 1000) {
            return false;
        }
        
        // Check for common Amazon error pages or captcha
        if (strpos($html, 'Robot Check') !== false || 
            strpos($html, 'captcha') !== false || 
            strpos($html, 'Access Denied') !== false ||
            strpos($html, 'Sorry, we just need to verify') !== false ||
            strpos($html, 'Enter the characters you see below') !== false) {
            return false;
        }
        
        // Check if it's actually an Amazon product page
        if (strpos($html, 'productTitle') === false && 
            strpos($html, 'a-price') === false && 
            strpos($html, 'amazon') === false) {
            return false;
        }
        
        return $html;
    }

    /**
     * Parse Amazon data from HTML
     */
    private function parseAmazonData($html)
    {
        $data = [];
        
        // Extract title - multiple patterns
        if (preg_match('/<span[^>]*id="productTitle"[^>]*>(.*?)<\/span>/s', $html, $matches)) {
            $data['name'] = trim(strip_tags($matches[1]));
        } elseif (preg_match('/<h1[^>]*class="a-size-large[^"]*"[^>]*>(.*?)<\/h1>/s', $html, $matches)) {
            $data['name'] = trim(strip_tags($matches[1]));
        } elseif (preg_match('/<title[^>]*>(.*?)<\/title>/s', $html, $matches)) {
            $title = trim(strip_tags($matches[1]));
            // Clean up Amazon title format
            $title = preg_replace('/\s*-\s*Amazon\.com.*$/', '', $title);
            $data['name'] = $title;
        }
        
        // Extract price
        $price = null;
        $priceCurrency = null;
        $htmlDecodeFlags = ENT_QUOTES | (defined('ENT_HTML5') ? ENT_HTML5 : 0);

        // Try to get price from screen-reader spans (most reliable on Amazon pages)
        if (preg_match_all('/<span[^>]*class="a-offscreen"[^>]*>([^<]+)<\/span>/', $html, $offscreenMatches)) {
            foreach ($offscreenMatches[1] as $value) {
                $cleanValue = trim(strip_tags(html_entity_decode($value, $htmlDecodeFlags, 'UTF-8')));

                if ($cleanValue === '') {
                    continue;
                }

                $numericValue = preg_replace('/[^0-9.,]/', '', $cleanValue);
                if ($numericValue === '') {
                    continue;
                }

                $amount = floatval(str_replace(',', '', $numericValue));
                if ($amount <= 0) {
                    continue; // skip placeholders like $0.00
                }

                $currency = null;
                if (stripos($cleanValue, 'AED') !== false || stripos($cleanValue, 'Ø¯.Ø¥') !== false || stripos($cleanValue, 'Ø¯Ø±Ù‡Ù…') !== false) {
                    $currency = 'AED';
                } elseif (strpos($cleanValue, '$') !== false || stripos($cleanValue, 'USD') !== false) {
                    $currency = 'USD';
                }

                if ($currency === 'AED') {
                    $price = $amount;
                    $priceCurrency = 'AED';
                    break;
                }

                if (is_null($price)) {
                    $price = $amount;
                    $priceCurrency = $currency;
                }
            }
        }

        // Fallback patterns if offscreen spans didn't give us a price
        if (is_null($price)) {
            $aedPatterns = [
                '/<span[^>]*class="a-offscreen"[^>]*>\\s*AED\\s*([0-9.,]+)<\\/span>/',
                '/<span[^>]*class="aok-offscreen"[^>]*>\\s*AED\\s*([0-9.,]+)<\\/span>/',
                '/<span[^>]*class="a-price"[^>]*>.*?AED\\s*([0-9.,]+).*?<\\/span>/s',
                '/<span[^>]*class="a-price-symbol"[^>]*>AED<\\/span><span[^>]*class="a-price-whole"[^>]*>([0-9.,]+)/',
                '/"displayPrice":"\\s*AED\\s*([0-9.,]+)"/',
                '/"displayString"[^>]*value="\\s*AED\\s*([0-9.,]+)"/',
                '/AED\\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\\.[0-9]{2})?)/',
            ];

            foreach ($aedPatterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $amount = floatval(str_replace(',', '', $matches[1]));
                    if ($amount > 0) {
                        $price = $amount;
                        $priceCurrency = 'AED';
                        break;
                    }
                }
            }
        }

        if (is_null($price)) {
            $usdPatterns = [
                '/<span[^>]*class="a-price"[^>]*>.*?\\$\\s*([0-9.,]+).*?<\\/span>/s',
                '/<span[^>]*class="a-offscreen"[^>]*>\\s*\\$\\s*([0-9.,]+)<\\/span>/',
                '/<span[^>]*class="a-price-range"[^>]*>\\s*\\$\\s*([0-9.,]+)[^<]*<\\/span>/',
                '/<span[^>]*class="a-price-symbol"[^>]*>\\$<\\/span><span[^>]*class="a-price-whole"[^>]*>([0-9.,]+)/',
                '/"price":\\s*"([0-9.,]+)"/',
                '/\\$([0-9]{1,3}(?:,[0-9]{3})*(?:\\.[0-9]{2})?)/',
            ];

            foreach ($usdPatterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $amount = floatval(str_replace(',', '', $matches[1]));
                    if ($amount > 0) {
                        $price = $amount;
                        $priceCurrency = 'USD';
                        break;
                    }
                }
            }
        }

        if (is_null($price)) {
            $genericPatterns = [
                '/data-price="([0-9.,]+)"/',
                '/name="[^"]*price[^"]*"[^>]*value="([0-9.,]+)"/',
            ];

            foreach ($genericPatterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $amount = floatval(str_replace(',', '', $matches[1]));
                    if ($amount > 0) {
                        $price = $amount;
                        break;
                    }
                }
            }
        }

        if (!is_null($price)) {
            if ($priceCurrency === 'AED' || (!$priceCurrency && (stripos($html, 'AED') !== false || stripos($html, 'Ø¯Ø±Ù‡Ù…') !== false))) {
                $data['price'] = Currency::convert((float) $price, 'AED', 'JOD');
                $data['original_price_aed'] = round($price, 2);
            } elseif ($priceCurrency === 'USD' || (!$priceCurrency && strpos($html, '$') !== false)) {
                $data['price'] = Currency::convert((float) $price, 'USD', 'JOD');
                $data['original_price_usd'] = round($price, 2);
            } else {
                $data['price'] = Currency::round((float) $price, 'JOD');
            }
        }
        
        // Extract rating - multiple patterns
        if (preg_match('/<span[^>]*class="a-icon-alt"[^>]*>([0-9.]+) out of 5 stars<\/span>/', $html, $matches)) {
            $data['rating'] = floatval($matches[1]);
        } elseif (preg_match('/<span[^>]*class="a-icon-alt"[^>]*>([0-9.]+) Ù…Ù† 5 Ù†Ø¬ÙˆÙ…<\/span>/', $html, $matches)) {
            $data['rating'] = floatval($matches[1]);
        } elseif (preg_match('/<span[^>]*class="a-icon-alt"[^>]*>([0-9.]+) de 5 estrellas<\/span>/', $html, $matches)) {
            $data['rating'] = floatval($matches[1]);
        } elseif (preg_match('/data-rating="([0-9.]+)"/', $html, $matches)) {
            $data['rating'] = floatval($matches[1]);
        } elseif (preg_match('/"ratingValue":\s*"([0-9.]+)"/', $html, $matches)) {
            $data['rating'] = floatval($matches[1]);
        }
        
        // Extract description (from bullet points)
        $description = '';
        if (preg_match_all('/<span[^>]*class="a-list-item"[^>]*>(.*?)<\/span>/s', $html, $matches)) {
            $bulletPoints = array_slice($matches[1], 0, 3); // Take first 3 bullet points
            $description = implode(' ', array_map('strip_tags', $bulletPoints));
        }
        
        if (empty($description) && preg_match('/<div[^>]*id="feature-bullets"[^>]*>(.*?)<\/div>/s', $html, $matches)) {
            $description = strip_tags($matches[1]);
            $description = preg_replace('/\s+/', ' ', $description);
        }
        
        $data['description'] = trim($description);
        
        // Extract gallery images
        $galleryImages = [];

        if (preg_match('/data-a-dynamic-image="([^"]+)"/i', $html, $matches)) {
            $json = html_entity_decode($matches[1], ENT_QUOTES | (defined('ENT_HTML5') ? ENT_HTML5 : 0), 'UTF-8');
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                foreach (array_keys($decoded) as $imageUrl) {
                    $cleanUrl = $this->sanitizeRemoteImageUrl($imageUrl);
                    if ($cleanUrl) {
                        $galleryImages[] = $cleanUrl;
                    }
                }
            }
        }

        $additionalGalleryPatterns = [
            '/"hiRes"\s*:\s*"([^"]+)"/i',
            '/"large"\s*:\s*"([^"]+)"/i',
            '/"mainUrl"\s*:\s*"([^"]+)"/i',
            '/"thumbUrl"\s*:\s*"([^"]+)"/i',
            '/"zoom"\s*:\s*"([^"]+)"/i',
            '/data-old-hires="([^"]+)"/i',
            "/data-old-hires='([^']+)'/i",
        ];

        foreach ($additionalGalleryPatterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $imageUrl) {
                    $cleanUrl = $this->sanitizeRemoteImageUrl($imageUrl);
                    if ($cleanUrl) {
                        $galleryImages[] = $cleanUrl;
                    }
                }
            }
        }

        $primaryCandidates = [];
        if (preg_match('/<img[^>]*id="landingImage"[^>]*src="([^"]+)"[^>]*>/', $html, $matches)) {
            $cleanUrl = $this->sanitizeRemoteImageUrl($matches[1]);
            if ($cleanUrl) {
                $primaryCandidates[] = $cleanUrl;
            }
        }
        if (empty($primaryCandidates) && preg_match('/<img[^>]*data-old-hires="([^"]+)"[^>]*>/', $html, $matches)) {
            $cleanUrl = $this->sanitizeRemoteImageUrl($matches[1]);
            if ($cleanUrl) {
                $primaryCandidates[] = $cleanUrl;
            }
        }

        if (!empty($primaryCandidates)) {
            $data['image'] = $primaryCandidates[0];
            $galleryImages = array_merge($primaryCandidates, $galleryImages);
        }

        $galleryImages = array_values(array_unique($galleryImages));
        if (!empty($galleryImages)) {
            $data['gallery_images'] = array_slice($galleryImages, 0, 10);
            if (empty($data['image'])) {
                $data['image'] = $data['gallery_images'][0];
            }
        }
        
        return $data;
    }

    /**
     * Download and save image from URL
     */
    private function downloadAndSaveImage($imageUrl)
    {
        try {
            $sanitizedUrl = $this->sanitizeRemoteImageUrl($imageUrl);
            if (!$sanitizedUrl) {
                return null;
            }

            // Ù…Ø­Ø§ÙˆÙ„Ø© Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø¶ØºØ· Ø§Ù„ØµÙˆØ± Ø§Ù„Ù…ØªÙ‚Ø¯Ù… Ø£ÙˆÙ„Ø§Ù‹
            if (extension_loaded('gd')) {
                return ImageCompressionService::compressFromUrl(
                    $sanitizedUrl,
                    'tools',
                    80, // Ø¬ÙˆØ¯Ø© 80%
                    1200, // Ø£Ù‚ØµÙ‰ Ø¹Ø±Ø¶
                    1200  // Ø£Ù‚ØµÙ‰ Ø§Ø±ØªÙØ§Ø¹
                );
            } else {
                // Ø§Ø³ØªØ®Ø¯Ø§Ù… Ø§Ù„Ø­ÙØ¸ Ø§Ù„Ù…Ø¨Ø§Ø´Ø± Ø¥Ø°Ø§ Ù„Ù… ÙŠÙƒÙ† GD Ù…ØªÙˆÙØ±Ø§Ù‹
                return SimpleImageCompressionService::compressFromUrl(
                    $sanitizedUrl,
                    'tools'
                );
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to download and save image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download and save multiple gallery images
     *
     * @param array $imageUrls
     * @param int $limit
     * @return array
     */
    private function downloadAndSaveGalleryImages(array $imageUrls, int $limit = 6): array
    {
        $storedImages = [];
        $seen = [];

        foreach ($imageUrls as $url) {
            if (count($storedImages) >= $limit) {
                break;
            }

            $sanitized = $this->sanitizeRemoteImageUrl($url);
            if (!$sanitized || isset($seen[$sanitized])) {
                continue;
            }

            $seen[$sanitized] = true;

            $storedPath = $this->downloadAndSaveImage($sanitized);
            if ($storedPath) {
                $storedImages[] = $storedPath;
            }
        }

        return $storedImages;
    }

    /**
     * Merge primary image path into gallery ensuring uniqueness
     */
    private function mergePrimaryIntoGallery(?string $primary, array $gallery): array
    {
        $collection = collect($gallery);

        if ($primary) {
            $collection = $collection->prepend($primary);
        }

        return $collection
            ->filter()
            ->map(function ($path) {
                return ltrim($path, '/');
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Delete gallery images from storage
     */
    private function deleteGalleryImages(?array $paths, array $exclude = []): void
    {
        if (empty($paths) || !is_array($paths)) {
            return;
        }

        $excludeLookup = array_flip(array_map(function ($path) {
            return ltrim($path, '/');
        }, $exclude));

        foreach ($paths as $path) {
            if (empty($path)) {
                continue;
            }

            $normalized = ltrim($path, '/');
            if (isset($excludeLookup[$normalized])) {
                continue;
            }

            if (Storage::disk('public')->exists($normalized)) {
                Storage::disk('public')->delete($normalized);
            }
        }
    }

    /**
     * Ensure remote image URL is valid and absolute
     */
    private function sanitizeRemoteImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $decoded = html_entity_decode($url, ENT_QUOTES | (defined('ENT_HTML5') ? ENT_HTML5 : 0), 'UTF-8');
        $decoded = trim(str_replace(['\\/', '\\u0026'], ['/', '&'], $decoded));

        if ($decoded === '') {
            return null;
        }

        if (Str::startsWith($decoded, '//')) {
            $decoded = 'https:' . $decoded;
        }

        if (!Str::startsWith($decoded, ['http://', 'https://'])) {
            return null;
        }

        // Drop URL fragments to stabilise duplicates coming from the same asset.
        $decoded = Str::before($decoded, '#');

        $parts = parse_url($decoded);
        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'https';

        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        // Amazon image hosts all support HTTPS, so normalise to avoid duplicates.
        if ($scheme === 'http' && Str::contains($host, ['amazon.', 'amazonaws.', 'media-amazon', 'images-amazon'])) {
            $scheme = 'https';
        }

        $path = $parts['path'] ?? '';
        if ($path === '') {
            return null;
        }

        // Collapse repeated slashes to keep path canonical.
        $path = preg_replace('#/{2,}#', '/', $path);

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        if (Str::contains($host, ['amazon.', 'amazonaws.', 'media-amazon', 'images-amazon'])) {
            // Remove Amazon's size suffixes (e.g. ._AC_SX679_) to prevent same image variants.
            if (preg_match('/^(.*)\._[^\/]+_\.(jpe?g|png|gif|webp)$/i', $path, $matches)) {
                $path = $matches[1] . '.' . $matches[2];
            }
            $query = ''; // Amazon image URLs do not require query parameters.
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return "{$scheme}://{$host}{$port}{$path}{$query}";
    }
}
