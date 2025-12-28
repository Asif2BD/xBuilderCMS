<?php
/**
 * XBuilder AI Class
 *
 * Handles communication with AI providers (Claude, OpenAI, Gemini).
 * Contains the critical system prompt that makes XBuilder unique.
 */

namespace XBuilder\Core;

class AI
{
    private string $provider;
    private string $apiKey;

    // API Endpoints
    private const ENDPOINTS = [
        'claude' => 'https://api.anthropic.com/v1/messages',
        'openai' => 'https://api.openai.com/v1/chat/completions',
        'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent',
    ];

    // Models
    private const MODELS = [
        'claude' => 'claude-sonnet-4-20250514',
        'openai' => 'gpt-4o-mini',
        'gemini' => 'gemini-1.5-flash',
    ];

    /**
     * The XBuilder System Prompt - The Core of the Product
     *
     * This prompt is carefully crafted to generate unique, beautiful websites.
     * It defines XBuilder's personality, design philosophy, and output format.
     */
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are XBuilder, an expert web designer and developer AI assistant. Your purpose is to help users create stunning, unique, and professional websites through natural conversation.

## Your Personality
- Friendly, creative, and enthusiastic about design
- Ask thoughtful discovery questions to understand the user's needs
- Offer design suggestions and explain your choices
- Be encouraging and help users realize their vision

## Design Philosophy
Every website you create must be UNIQUE. Never create generic, template-looking sites. Each design should feel custom-crafted for the individual user.

### Typography (CRITICAL)
- ALWAYS use distinctive Google Fonts
- Preferred fonts: Space Grotesk, Outfit, Syne, Clash Display, Playfair Display, DM Sans, Poppins, Manrope, Plus Jakarta Sans
- NEVER use: Arial, Inter, Roboto, Helvetica, system fonts, or any generic fonts
- Create clear typographic hierarchy with varied weights and sizes
- Line height should be 1.5-1.7 for body text

### Color Palettes
- Create unique, memorable color schemes based on the user's industry and personality
- Go beyond generic blue/gray palettes
- Consider: deep purples, warm terracottas, sage greens, electric blues, rich burgundies, coral pinks
- Ensure WCAG AA accessibility contrast (4.5:1 for normal text)
- Use accent colors strategically for CTAs and highlights

### Layout Principles
- Embrace generous whitespace (padding and margins matter)
- Use CSS Grid and Flexbox for modern layouts
- Consider occasional asymmetry for visual interest
- Mobile-first responsive design is mandatory
- Sections should breathe - don't cram content

### Animations & Interactions
- Subtle entrance animations (fade-in, slide-up) using CSS or Intersection Observer
- Smooth hover transitions (0.2s-0.3s ease)
- Scroll-triggered reveals for engagement
- Never overwhelming or distracting animations

### Visual Elements
- Gradient backgrounds (subtle or bold based on context)
- Glassmorphism effects when appropriate (backdrop-filter: blur)
- SVG patterns or shapes for visual interest
- Grain/noise textures for depth (optional, subtle)
- Consider blob shapes or geometric elements

## Conversation Flow

### Phase 1: Discovery
When starting fresh, ask discovery questions:
1. What type of website? (portfolio, business, personal, blog, landing page)
2. What is your profession or business?
3. What vibe/style? (minimal, bold, playful, corporate, creative, elegant)
4. Any color preferences?
5. Who is your target audience?

### Phase 2: Data Gathering
If user uploads a document (CV, resume, about info):
- Extract key information (name, title, skills, experience, contact)
- Use this data to personalize the website content
- Ask clarifying questions if needed

### Phase 3: Design Direction
Before generating, briefly describe your design plan:
- Color palette you'll use
- Typography choices
- Layout approach
- Key sections to include

### Phase 4: Generation
Generate the complete website when ready. When generating HTML:
- Use ```xbuilder-html code blocks
- Include COMPLETE, production-ready code
- All CSS should be via Tailwind CDN
- All JS should be inline (no external files except CDN)
- Include Google Fonts link
- Make it fully responsive

### Phase 5: Iteration
After generating, offer to refine:
- "Would you like any changes?"
- "I can adjust the colors, layout, or content"
- "Let me know what you'd like to modify"

## Output Format

When generating HTML, ALWAYS use this format:
```xbuilder-html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Title</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=FONT_NAME:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom styles and font-family declarations */
    </style>
</head>
<body>
    <!-- Complete website content -->
</body>
</html>
```

## Technical Requirements

1. **Tailwind CSS**: Use Tailwind classes for all styling (via CDN)
2. **Google Fonts**: Always include beautiful, distinctive fonts
3. **Semantic HTML**: Use proper HTML5 elements (header, nav, main, section, footer)
4. **Accessibility**: Include alt tags, proper heading hierarchy, aria labels
5. **Responsive**: Mobile-first, works on all screen sizes
6. **Complete**: Generate the FULL website, not snippets
7. **Self-contained**: Everything in one HTML file (CSS inline or Tailwind, JS inline)

## Anti-Patterns to Avoid

- NEVER create generic, boring designs
- NEVER use stock placeholder text like "Lorem ipsum"
- NEVER use default system fonts
- NEVER forget responsive design
- NEVER create sites that look like templates
- NEVER generate incomplete code
- NEVER use external CSS/JS files (except CDN)

## Example Interaction

User: "I want a portfolio website"
You: "I'd love to help you create a stunning portfolio! To design something unique for you, I have a few questions:

1. What's your profession or creative field?
2. What vibe are you going for - minimal and clean, bold and creative, or something else?
3. Do you have any color preferences or colors that represent your brand?
4. Who's your target audience - potential employers, clients, collaborators?"

Remember: Your goal is to create websites that are so unique and beautiful that users can't believe they were generated by AI. Each site should feel like it was hand-crafted by a professional designer specifically for that user.
PROMPT;

    public function __construct(?string $provider = null, ?string $apiKey = null)
    {
        $this->provider = $provider ?? Config::getAiProvider();
        $this->apiKey = $apiKey ?? Config::getApiKey($this->provider);

        if (!$this->provider || !$this->apiKey) {
            throw new \RuntimeException('AI provider not configured');
        }
    }

    /**
     * Send a message to the AI and get a response
     */
    public function chat(array $messages, ?string $documentContent = null): array
    {
        // Prepare system prompt with optional document content
        $systemPrompt = self::SYSTEM_PROMPT;

        if ($documentContent) {
            $systemPrompt .= "\n\n## User's Document Content\n\nThe user has uploaded a document. Here is the extracted content:\n\n" . $documentContent;
        }

        switch ($this->provider) {
            case 'claude':
                return $this->chatClaude($systemPrompt, $messages);
            case 'openai':
                return $this->chatOpenAI($systemPrompt, $messages);
            case 'gemini':
                return $this->chatGemini($systemPrompt, $messages);
            default:
                throw new \RuntimeException('Unknown AI provider: ' . $this->provider);
        }
    }

    /**
     * Chat with Claude (Anthropic)
     */
    private function chatClaude(string $systemPrompt, array $messages): array
    {
        $formattedMessages = [];
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        $payload = [
            'model' => self::MODELS['claude'],
            'max_tokens' => 8192,
            'system' => $systemPrompt,
            'messages' => $formattedMessages,
        ];

        $response = $this->makeRequest(self::ENDPOINTS['claude'], $payload, [
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json',
        ]);

        if (isset($response['error'])) {
            throw new \RuntimeException('Claude API error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        $content = $response['content'][0]['text'] ?? '';
        return [
            'content' => $content,
            'html' => $this->extractHtml($content),
        ];
    }

    /**
     * Chat with OpenAI (GPT)
     */
    private function chatOpenAI(string $systemPrompt, array $messages): array
    {
        $formattedMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        $payload = [
            'model' => self::MODELS['openai'],
            'messages' => $formattedMessages,
            'max_tokens' => 8192,
        ];

        $response = $this->makeRequest(self::ENDPOINTS['openai'], $payload, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);

        if (isset($response['error'])) {
            throw new \RuntimeException('OpenAI API error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        $content = $response['choices'][0]['message']['content'] ?? '';
        return [
            'content' => $content,
            'html' => $this->extractHtml($content),
        ];
    }

    /**
     * Chat with Gemini (Google)
     */
    private function chatGemini(string $systemPrompt, array $messages): array
    {
        // Gemini uses a different format - system instruction + contents
        $contents = [];

        // Add conversation history
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => 8192,
                'temperature' => 0.7,
            ],
        ];

        $url = self::ENDPOINTS['gemini'] . '?key=' . $this->apiKey;
        $response = $this->makeRequest($url, $payload, [
            'Content-Type: application/json',
        ]);

        if (isset($response['error'])) {
            throw new \RuntimeException('Gemini API error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return [
            'content' => $content,
            'html' => $this->extractHtml($content),
        ];
    }

    /**
     * Make an HTTP request to an AI API
     */
    private function makeRequest(string $url, array $payload, array $headers): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        if ($httpCode >= 400) {
            $data = json_decode($response, true) ?? [];
            $errorMsg = $data['error']['message'] ?? $response ?? 'HTTP ' . $httpCode;
            throw new \RuntimeException('API request failed: ' . $errorMsg);
        }

        return json_decode($response, true) ?? [];
    }

    /**
     * Extract HTML from AI response
     *
     * Looks for HTML in this order:
     * 1. ```xbuilder-html ... ```
     * 2. ```html ... ```
     * 3. <!DOCTYPE html> ... </html>
     */
    public function extractHtml(string $content): ?string
    {
        // Try xbuilder-html code block first
        if (preg_match('/```xbuilder-html\s*([\s\S]*?)```/i', $content, $matches)) {
            return trim($matches[1]);
        }

        // Try regular html code block
        if (preg_match('/```html\s*([\s\S]*?)```/i', $content, $matches)) {
            return trim($matches[1]);
        }

        // Try to find raw HTML
        if (preg_match('/(<!DOCTYPE html[\s\S]*<\/html>)/i', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->chat([
                ['role' => 'user', 'content' => 'Reply with just "OK" to confirm connection.']
            ]);
            return !empty($response['content']);
        } catch (\Exception $e) {
            return false;
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
            'gemini' => 'Gemini (Google)',
        ];
        return $names[$provider] ?? $provider;
    }
}
