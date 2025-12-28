<?php
/**
 * XBuilder AI Class
 * 
 * Handles communication with AI providers:
 * - Claude (Anthropic)
 * - Gemini (Google)
 * - ChatGPT (OpenAI)
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
        'gemini' => 'gemini-1.5-flash',
        'openai' => 'gpt-4o-mini'
    ];
    
    // API endpoints
    private array $endpoints = [
        'claude' => 'https://api.anthropic.com/v1/messages',
        'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
        'openai' => 'https://api.openai.com/v1/chat/completions'
    ];
    
    public function __construct(string $provider)
    {
        $this->security = new Security();
        $this->provider = $provider;
        $this->apiKey = $this->security->getApiKey($provider);
    }
    
    /**
     * Get the system prompt for XBuilder
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

### Typography
- Use Google Fonts for personality (e.g., Space Grotesk, Outfit, Syne, Clash Display, Cabinet Grotesk, Satoshi)
- Never use generic fonts like Arial, Inter, or Roboto
- Establish clear hierarchy with font sizes
- Line height 1.5-1.7 for readability

### Color
- Create unique color palettes, not generic blue/gray
- Consider the person's industry, personality, vibe
- Use accent colors purposefully
- Ensure sufficient contrast for accessibility
- Be creative: deep purples, warm terracottas, sage greens, electric blues

### Layout
- Embrace whitespace - let content breathe
- Use CSS Grid and Flexbox for modern layouts
- Break the grid occasionally for visual interest
- Asymmetry can be beautiful

### Animation
- Subtle entrance animations (fade, slide)
- Smooth hover transitions
- Scroll-triggered reveals using Intersection Observer
- Never overwhelming or distracting

### Visual Elements
- Custom gradient backgrounds when appropriate
- Glassmorphism, neumorphism, or other modern effects when fitting
- SVG patterns or shapes for uniqueness
- Grain textures, noise overlays for depth

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
- Include ALL CSS in a <style> tag
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
    
    <!-- Open Graph -->
    <meta property="og:title" content="Title">
    <meta property="og:description" content="Description">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=FONT_NAME&display=swap" rel="stylesheet">
    
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
    <!-- Content -->
    
    <script>
        // JavaScript
    </script>
</body>
</html>
```

### Must Include
- Semantic HTML (header, main, section, footer)
- Mobile-responsive design
- Smooth scroll behavior
- Hover states on interactive elements
- At least one animation (entrance, scroll, or hover)

### JavaScript Features
- Mobile menu toggle
- Smooth scrolling for anchor links
- Scroll-triggered animations (Intersection Observer)
- Any interactivity the design needs

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
8. Use emoji favicon trick: <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚀</text></svg>">

## STARTING THE CONVERSATION

When a user first arrives, greet them warmly and ask what kind of website they'd like to create. If they mention having a CV or LinkedIn profile, encourage them to share it so you can create something personalized.
PROMPT;
    }
    
    /**
     * Send a message to the AI
     */
    public function chat(array $messages, ?string $documentContent = null): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'API key not configured for ' . $this->provider
            ];
        }
        
        // Add document content to the context if provided
        if ($documentContent) {
            $messages[] = [
                'role' => 'user',
                'content' => "Here is the content from my uploaded document:\n\n" . $documentContent
            ];
        }
        
        try {
            switch ($this->provider) {
                case 'claude':
                    return $this->callClaude($messages);
                case 'gemini':
                    return $this->callGemini($messages);
                case 'openai':
                    return $this->callOpenAI($messages);
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
    private function callClaude(array $messages): array
    {
        $payload = [
            'model' => $this->models['claude'],
            'max_tokens' => 8192,
            'system' => $this->getSystemPrompt(),
            'messages' => $this->formatMessagesForClaude($messages)
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
        
        return [
            'success' => true,
            'message' => $content,
            'html' => $this->extractHtml($content)
        ];
    }
    
    /**
     * Call Gemini API
     */
    private function callGemini(array $messages): array
    {
        $url = str_replace(
            '{model}',
            $this->models['gemini'],
            $this->endpoints['gemini']
        ) . '?key=' . $this->apiKey;
        
        $contents = $this->formatMessagesForGemini($messages);
        
        // Add system prompt as first exchange
        array_unshift($contents, [
            'role' => 'user',
            'parts' => [['text' => $this->getSystemPrompt()]]
        ]);
        array_splice($contents, 1, 0, [[
            'role' => 'model',
            'parts' => [['text' => 'I understand. I am XBuilder, ready to create beautiful, unique websites through conversation. I will follow all the design principles and output complete HTML code in the specified format.']]
        ]]);
        
        $payload = ['contents' => $contents];
        
        $response = $this->httpPost($url, $payload, [
            'Content-Type: application/json'
        ]);
        
        $data = json_decode($response, true);
        
        if (isset($data['error'])) {
            return [
                'success' => false,
                'error' => $data['error']['message'] ?? 'Unknown error'
            ];
        }
        
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        return [
            'success' => true,
            'message' => $content,
            'html' => $this->extractHtml($content)
        ];
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI(array $messages): array
    {
        $formattedMessages = [
            ['role' => 'system', 'content' => $this->getSystemPrompt()]
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
        
        return [
            'success' => true,
            'message' => $content,
            'html' => $this->extractHtml($content)
        ];
    }
    
    /**
     * Format messages for Claude API
     */
    private function formatMessagesForClaude(array $messages): array
    {
        return array_map(function($msg) {
            return [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content']
            ];
        }, $messages);
    }
    
    /**
     * Format messages for Gemini API
     */
    private function formatMessagesForGemini(array $messages): array
    {
        return array_map(function($msg) {
            return [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]]
            ];
        }, $messages);
    }
    
    /**
     * Extract HTML from AI response
     */
    private function extractHtml(string $content): ?string
    {
        // Look for ```xbuilder-html ... ``` blocks
        if (preg_match('/```xbuilder-html\s*([\s\S]*?)```/i', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Fallback: look for ```html ... ``` blocks
        if (preg_match('/```html\s*([\s\S]*?)```/i', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Fallback: look for <!DOCTYPE html> ... </html>
        if (preg_match('/(<!DOCTYPE html>[\s\S]*<\/html>)/i', $content, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
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
            CURLOPT_TIMEOUT => 120, // 2 minute timeout for generation
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
        $testMessage = [['role' => 'user', 'content' => 'Say "API working" in exactly 2 words.']];
        
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
}
