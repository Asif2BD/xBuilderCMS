<?php
/**
 * XBuilder AI Class
 *
 * Handles communication with AI providers:
 * - Claude (Anthropic)
 * - Gemini (Google)
 * - ChatGPT (OpenAI)
 *
 * Combined best practices:
 * - Instance-based for DI/testing
 * - Enhanced system prompt for unique designs
 * - API key validation and testing
 * - Provider name helper
 */

namespace XBuilder\Core;

class AI
{
    private Security $security;
    private string $provider;
    private ?string $apiKey;

    // Model configurations
    private array $models = [
        'claude' => 'claude-sonnet-4-20250514',
        'gemini' => 'gemini-2.5-flash',
        'openai' => 'gpt-4o-mini'
    ];

    // API endpoints
    private array $endpoints = [
        'claude' => 'https://api.anthropic.com/v1/messages',
        'gemini' => 'https://generativelanguage.googleapis.com/v1/models/{model}:generateContent',
        'openai' => 'https://api.openai.com/v1/chat/completions'
    ];

    public function __construct(string $provider, ?Security $security = null)
    {
        $this->security = $security ?? new Security();
        $this->provider = $provider;
        $this->apiKey = $this->security->getApiKey($provider);
    }

    /**
     * Get the system prompt for XBuilder
     * This is the CORE of the product - carefully crafted for unique designs
     */
    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are XBuilder, an expert web designer and developer AI assistant. Your purpose is to create stunning, unique, professional websites through conversation.

## YOUR CAPABILITIES

1. **Understand user needs** through natural conversation
2. **Extract information** from CVs, LinkedIn profiles, and documents
3. **Design unique websites** - never generic or template-looking
4. **Generate production-ready code** in HTML, CSS, and JavaScript
5. **Iterate and refine** based on user feedback

## DESIGN PHILOSOPHY

You believe in:
- **Uniqueness**: Every website should feel custom-crafted, not templated
- **Modern aesthetics**: Clean typography, purposeful whitespace, subtle animations
- **Personality**: The site should reflect the person/brand, not look generic
- **Performance**: Fast-loading, optimized code
- **Mobile-first**: Perfect on all devices

## DESIGN PRINCIPLES

### Typography (CRITICAL)
- Use Google Fonts for personality (e.g., Space Grotesk, Outfit, Syne, Clash Display, Cabinet Grotesk, Satoshi, Playfair Display, DM Sans, Poppins, Manrope, Plus Jakarta Sans)
- NEVER use generic fonts like Arial, Inter, Roboto, Helvetica, or system fonts
- Establish clear hierarchy with font sizes and weights
- Line height 1.5-1.7 for readability

### Color Palettes
- Create unique color palettes, not generic blue/gray
- Consider the person's industry, personality, vibe
- Be creative: deep purples, warm terracottas, sage greens, electric blues, rich burgundies, coral pinks
- Use accent colors purposefully for CTAs
- Ensure WCAG AA accessibility contrast (4.5:1 for normal text)

### Layout Principles
- Embrace whitespace - let content breathe
- Use CSS Grid and Flexbox for modern layouts
- Break the grid occasionally for visual interest
- Asymmetry can be beautiful
- Mobile-first responsive design

### Animation & Interactions
- Subtle entrance animations (fade, slide)
- Smooth hover transitions (0.2s-0.3s ease)
- Scroll-triggered reveals using Intersection Observer
- Never overwhelming or distracting

### Visual Elements
- Custom gradient backgrounds when appropriate
- Glassmorphism, neumorphism, or other modern effects when fitting
- SVG patterns or shapes for uniqueness
- Grain textures, noise overlays for depth (optional, subtle)

## CONVERSATION APPROACH

### Phase 1: Discovery
Ask about (naturally, not all at once):
- What's the website for? (portfolio, business, personal brand)
- Who is the target audience?
- What feeling should visitors get? (professional, creative, friendly, bold)
- Any websites they admire?
- Color preferences or brand colors?
- Dark mode or light mode preference?

### Phase 2: Data Gathering
If user provides CV/LinkedIn/documents:
- Extract key information enthusiastically
- Identify highlights and achievements
- Note skills, experience, education
- Find personality indicators
- Summarize what you found and confirm

**CRITICAL VALIDATION**:
- If the uploaded document is corrupted, unreadable, or contains less than 50 words of meaningful content, DO NOT generate a website
- Instead, say: "I couldn't extract meaningful content from that file. Could you try uploading a DOCX version, or tell me about yourself directly?"
- NEVER generate a blank or generic website with placeholder content
- If you don't have real information about the user, ASK for it instead of guessing

### Phase 3: Design Direction
Before generating, briefly describe:
- The overall vibe/aesthetic you'll create
- Color palette you're thinking
- Layout approach
- Any special features
- Ask for approval or adjustments

### Phase 4: Generation
Generate the complete website using the exact format below.

### Phase 5: Iteration
After showing the site:
- Ask what they think
- Offer specific improvements
- Be ready to change anything
- Suggest enhancements they might not have thought of

## CODE OUTPUT FORMAT

CRITICAL: When generating or updating the website, you MUST use this exact format:

```xbuilder-html
<!DOCTYPE html>
<html lang="en">
<!-- Complete website code here -->
</html>
```

The code block MUST:
- Start with ```xbuilder-html
- End with ```
- Contain the COMPLETE HTML file
- Include ALL CSS in a <style> tag or via Tailwind
- Include ALL JavaScript in a <script> tag
- Be a fully working single-file website

## TECHNICAL REQUIREMENTS

### HTML Structure
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Title</title>
    <meta name="description" content="Site description">

    <!-- Emoji Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚀</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=FONT_NAME:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { /* custom colors */ },
                    fontFamily: { /* custom fonts */ }
                }
            }
        }
    </script>

    <style>
        /* Custom CSS */
    </style>
</head>
<body>
    <!-- Content with semantic HTML -->

    <script>
        // JavaScript for interactivity
    </script>
</body>
</html>
```

### Must Include
- Semantic HTML (header, main, section, footer)
- **CRITICAL: Mobile-responsive design** - MUST work perfectly on phones (320px+), tablets, and desktops
- Viewport meta tag: `<meta name="viewport" content="width=device-width, initial-scale=1.0">`
- Use Tailwind responsive classes (sm:, md:, lg:, xl:) for all layouts
- Mobile menu toggle (hamburger menu for mobile, full nav for desktop)
- Smooth scroll behavior
- Hover states on interactive elements
- At least one animation (entrance, scroll, or hover)
- Scroll-triggered animations (Intersection Observer)
- Test breakpoints: Mobile (< 640px), Tablet (640-1024px), Desktop (> 1024px)

## DESIGN VARIATIONS

Create DIFFERENT aesthetics based on context:

### For Creatives (Designers, Artists)
- Bold, experimental layouts
- Large imagery
- Unique color choices
- Artistic typography

### For Tech Professionals
- Clean, sophisticated design
- Dark mode works well
- Subtle code/tech references
- Project showcases with tech stacks

### For Business/Corporate
- Professional, trustworthy
- Clear hierarchy
- Social proof prominent
- Strong calls-to-action

### For Personal Brands
- Warm, approachable
- Personal story featured
- Testimonials
- Clear service offerings

## IMPORTANT RULES

1. NEVER create generic-looking websites
2. ALWAYS use unique color combinations
3. ALWAYS include custom fonts from Google Fonts
4. ALWAYS add at least subtle animations
5. ALWAYS make it mobile-responsive
6. ALWAYS output COMPLETE, working HTML
7. NEVER use placeholder images - use gradients, patterns, or SVGs instead
8. NEVER use Lorem ipsum - create realistic placeholder content
9. Use emoji favicon trick for quick personalization

## THE XBUILDER INTERFACE (CRITICAL)

**IMPORTANT**: You are integrated into the XBuilder CMS, NOT a standalone chatbot.

When you generate code using the ```xbuilder-html format:

1. **Preview Tab**: The website automatically appears in a live preview iframe
2. **Code Tab**: The HTML source code is displayed for review
3. **Publish Button**: A "Publish to Live Site" button appears automatically
4. **Deployment**: When clicked, the site is deployed to the user's root domain

**NEVER tell users to:**
- ❌ "Copy the code block and paste into a text editor"
- ❌ "Save as index.html and open in browser"
- ❌ "Deploy using Netlify, Vercel, etc."

**INSTEAD, say:**
- ✅ "Check the Preview tab to see your website!"
- ✅ "The website is ready - check it out in the Preview tab!"
- ✅ "When you're happy with it, click 'Publish to Live Site' to deploy!"
- ✅ "Your code is ready in the Code tab if you want to review it"

**The XBuilder interface handles everything automatically.** Your job is to generate great code and guide the user through refinements.

## STARTING THE CONVERSATION

When a user first arrives, greet them warmly and ask what kind of website they'd like to create. If they mention having a CV or LinkedIn profile, encourage them to share it so you can create something personalized.
PROMPT;
    }

    /**
     * Send a message to the AI and get a response
     */
    public function chat(array $messages, ?string $documentContent = null): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'API key not configured for ' . $this->provider
            ];
        }

        // Log document content status
        if ($documentContent) {
            error_log("[XBuilder AI] Document provided: " . strlen($documentContent) . " chars, preview: " . substr($documentContent, 0, 100));
        } else {
            error_log("[XBuilder AI] No document content provided to AI");
        }

        // Add document content to the context if provided
        $systemPrompt = $this->getSystemPrompt();
        if ($documentContent) {
            $systemPrompt .= "\n\n## User's Document Content\n\nThe user has uploaded a document. Here is the extracted content:\n\n" . $documentContent;
            error_log("[XBuilder AI] Added document to system prompt, total prompt length: " . strlen($systemPrompt));
        }

        try {
            switch ($this->provider) {
                case 'claude':
                    return $this->callClaude($systemPrompt, $messages);
                case 'gemini':
                    return $this->callGemini($systemPrompt, $messages);
                case 'openai':
                    return $this->callOpenAI($systemPrompt, $messages);
                default:
                    return ['success' => false, 'error' => 'Unknown provider'];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Call Claude API
     */
    private function callClaude(string $systemPrompt, array $messages): array
    {
        $formattedMessages = [];
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content']
            ];
        }

        $payload = [
            'model' => $this->models['claude'],
            'max_tokens' => 8192,
            'system' => $systemPrompt,
            'messages' => $formattedMessages
        ];

        $response = $this->httpPost(
            $this->endpoints['claude'],
            $payload,
            [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json'
            ]
        );

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            return [
                'success' => false,
                'error' => $data['error']['message'] ?? 'Unknown error'
            ];
        }

        $content = $data['content'][0]['text'] ?? '';

        $extractedHtml = $this->extractHtml($content);

        if ($extractedHtml) {
            error_log("[XBuilder AI] Successfully extracted HTML from Claude response (" . strlen($extractedHtml) . " chars)");
        } else {
            error_log("[XBuilder AI] WARNING: No HTML extracted from Claude response");
        }

        return [
            'success' => true,
            'message' => $content,
            'html' => $extractedHtml
        ];
    }

    /**
     * Call Gemini API
     */
    private function callGemini(string $systemPrompt, array $messages): array
    {
        $url = str_replace(
            '{model}',
            $this->models['gemini'],
            $this->endpoints['gemini']
        ) . '?key=' . $this->apiKey;

        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]]
            ];
        }

        // Add system prompt as first exchange for Gemini
        array_unshift($contents, [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]]
        ]);
        array_splice($contents, 1, 0, [[
            'role' => 'model',
            'parts' => [['text' => 'I understand. I am XBuilder, ready to create beautiful, unique websites through conversation. I will follow all the design principles and output complete HTML code in the specified format.']]
        ]]);

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => 8192,
                'temperature' => 0.7
            ]
        ];

        $response = $this->httpPost($url, $payload, ['Content-Type: application/json']);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            return [
                'success' => false,
                'error' => $data['error']['message'] ?? 'Unknown error'
            ];
        }

        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $extractedHtml = $this->extractHtml($content);

        if ($extractedHtml) {
            error_log("[XBuilder AI] Successfully extracted HTML from Gemini response (" . strlen($extractedHtml) . " chars)");
        } else {
            error_log("[XBuilder AI] WARNING: No HTML extracted from Gemini response");
        }

        return [
            'success' => true,
            'message' => $content,
            'html' => $extractedHtml
        ];
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $systemPrompt, array $messages): array
    {
        $formattedMessages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }

        $payload = [
            'model' => $this->models['openai'],
            'messages' => $formattedMessages,
            'max_tokens' => 8192
        ];

        $response = $this->httpPost(
            $this->endpoints['openai'],
            $payload,
            [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ]
        );

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            return [
                'success' => false,
                'error' => $data['error']['message'] ?? 'Unknown error'
            ];
        }

        $content = $data['choices'][0]['message']['content'] ?? '';

        $extractedHtml = $this->extractHtml($content);

        if ($extractedHtml) {
            error_log("[XBuilder AI] Successfully extracted HTML from OpenAI response (" . strlen($extractedHtml) . " chars)");
        } else {
            error_log("[XBuilder AI] WARNING: No HTML extracted from OpenAI response");
        }

        return [
            'success' => true,
            'message' => $content,
            'html' => $extractedHtml
        ];
    }

    /**
     * Extract HTML from AI response
     */
    private function extractHtml(string $content): ?string
    {
        error_log("[XBuilder AI] Attempting to extract HTML from response (length: " . strlen($content) . ")");

        // Look for ```xbuilder-html ... ``` blocks (most specific)
        // Handle both with and without newline after marker
        if (preg_match('/```xbuilder-html\s*\n?([\s\S]*?)```/i', $content, $matches)) {
            error_log("[XBuilder AI] Found xbuilder-html code block");
            return $this->cleanHtml(trim($matches[1]));
        }

        // Fallback: look for ```html ... ``` blocks
        if (preg_match('/```html\s*\n?([\s\S]*?)```/i', $content, $matches)) {
            error_log("[XBuilder AI] Found html code block");
            return $this->cleanHtml(trim($matches[1]));
        }

        // Fallback: look for <!DOCTYPE html> ... </html> anywhere in content
        // This catches cases where code block markers are malformed
        if (preg_match('/(<!DOCTYPE html>[\s\S]*?<\/html>)/i', $content, $matches)) {
            error_log("[XBuilder AI] Found DOCTYPE html pattern (no code block)");
            return $this->cleanHtml(trim($matches[1]));
        }

        // Last resort: look for incomplete HTML (starts with <!DOCTYPE but no closing)
        // This handles truncated responses
        if (preg_match('/(<!DOCTYPE html>[\s\S]+)/i', $content, $matches)) {
            error_log("[XBuilder AI] Found incomplete DOCTYPE html (response may be truncated)");
            $html = trim($matches[1]);
            // Add closing tags if missing
            if (!str_contains($html, '</html>')) {
                $html .= "\n</body>\n</html>";
            }
            if (!str_contains($html, '</body>')) {
                $html = str_replace('</html>', "</body>\n</html>", $html);
            }
            return $this->cleanHtml($html);
        }

        error_log("[XBuilder AI] No HTML found in response");
        return null;
    }

    /**
     * Clean up extracted HTML
     */
    private function cleanHtml(string $html): string
    {
        // Remove any leftover markdown code block markers
        $html = preg_replace('/^```[\w-]*\s*/m', '', $html);
        $html = preg_replace('/```\s*$/m', '', $html);

        return trim($html);
    }

    /**
     * Make HTTP POST request
     */
    private function httpPost(string $url, array $data, array $headers): string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        return $response;
    }

    /**
     * Test API key validity
     */
    public function testApiKey(): array
    {
        if (!$this->apiKey) {
            return ['valid' => false, 'error' => 'No API key configured'];
        }

        try {
            switch ($this->provider) {
                case 'claude':
                    $response = $this->httpPost(
                        $this->endpoints['claude'],
                        [
                            'model' => $this->models['claude'],
                            'max_tokens' => 20,
                            'messages' => [['role' => 'user', 'content' => 'Say "OK"']]
                        ],
                        [
                            'x-api-key: ' . $this->apiKey,
                            'anthropic-version: 2023-06-01',
                            'Content-Type: application/json'
                        ]
                    );
                    break;

                case 'gemini':
                    $url = str_replace('{model}', $this->models['gemini'], $this->endpoints['gemini'])
                         . '?key=' . $this->apiKey;
                    $response = $this->httpPost($url, [
                        'contents' => [['role' => 'user', 'parts' => [['text' => 'Say OK']]]]
                    ], ['Content-Type: application/json']);
                    break;

                case 'openai':
                    $response = $this->httpPost(
                        $this->endpoints['openai'],
                        [
                            'model' => $this->models['openai'],
                            'messages' => [['role' => 'user', 'content' => 'Say OK']],
                            'max_tokens' => 10
                        ],
                        [
                            'Authorization: Bearer ' . $this->apiKey,
                            'Content-Type: application/json'
                        ]
                    );
                    break;

                default:
                    return ['valid' => false, 'error' => 'Unknown provider'];
            }

            $data = json_decode($response, true);

            if (isset($data['error'])) {
                return ['valid' => false, 'error' => $data['error']['message'] ?? 'Invalid API key'];
            }

            return ['valid' => true];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get the current provider
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Get provider display name
     */
    public static function getProviderName(string $provider): string
    {
        $names = [
            'claude' => 'Claude (Anthropic)',
            'openai' => 'ChatGPT (OpenAI)',
            'gemini' => 'Gemini (Google)'
        ];
        return $names[$provider] ?? $provider;
    }
}
