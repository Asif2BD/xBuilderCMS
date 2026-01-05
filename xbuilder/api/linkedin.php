<?php
/**
 * XBuilder LinkedIn Profile Fetcher
 *
 * Fetches and parses public LinkedIn profiles without API access
 * Uses web scraping to extract profile information
 */

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['url']) || empty($input['url'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'LinkedIn URL required']);
    exit;
}

$linkedinUrl = trim($input['url']);

// Log the request
error_log("[XBuilder LinkedIn] Fetching profile: $linkedinUrl");

// Validate LinkedIn URL
if (!preg_match('/linkedin\.com\/(in|pub)\//', $linkedinUrl)) {
    error_log("[XBuilder LinkedIn] Invalid URL format");
    echo json_encode(['success' => false, 'error' => 'Invalid LinkedIn profile URL']);
    exit;
}

try {
    // Fetch the LinkedIn profile page
    $html = fetchLinkedInProfile($linkedinUrl);

    if (!$html) {
        error_log("[XBuilder LinkedIn] Failed to fetch HTML (empty response)");
        echo json_encode(['success' => false, 'error' => 'Could not fetch LinkedIn profile. Please try uploading your CV instead.']);
        exit;
    }

    error_log("[XBuilder LinkedIn] Fetched " . strlen($html) . " bytes of HTML");

    // Parse profile data
    $profileData = parseLinkedInProfile($html, $linkedinUrl);

    error_log("[XBuilder LinkedIn] Parsed - Name: " . ($profileData['name'] ?? 'none'));

    if (empty($profileData['name']) && empty($profileData['headline'])) {
        error_log("[XBuilder LinkedIn] No data extracted - might be login wall");
        echo json_encode(['success' => false, 'error' => 'Could not extract profile data. Please upload your CV instead.']);
        exit;
    }

    // Format as readable text
    $content = formatProfileData($profileData);

    echo json_encode([
        'success' => true,
        'content' => $content,
        'structured' => $profileData,
        'length' => strlen($content)
    ]);

} catch (Exception $e) {
    error_log("[XBuilder LinkedIn] Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch profile. Please upload your CV instead.'
    ]);
}

/**
 * Fetch LinkedIn profile HTML with proper headers
 */
function fetchLinkedInProfile(string $url): ?string
{
    $ch = curl_init();

    // Use comprehensive browser headers to avoid detection
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Cache-Control: max-age=0',
        ],
        CURLOPT_ENCODING => '',
        CURLOPT_COOKIEJAR => '/tmp/linkedin_cookies.txt',
        CURLOPT_COOKIEFILE => '/tmp/linkedin_cookies.txt',
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("[XBuilder LinkedIn] cURL error: $error");
        return null;
    }

    if ($httpCode !== 200) {
        error_log("[XBuilder LinkedIn] HTTP $httpCode received");
        return null;
    }

    if (!$html || strlen($html) < 1000) {
        error_log("[XBuilder LinkedIn] Response too short: " . strlen($html) . " bytes");
        return null;
    }

    return $html;
}

/**
 * Parse LinkedIn profile HTML - extract whatever we can
 */
function parseLinkedInProfile(string $html, string $url): array
{
    $data = [
        'name' => '',
        'headline' => '',
        'location' => '',
        'about' => '',
        'url' => $url
    ];

    // Strategy 1: Extract from <title> tag (most reliable)
    if (preg_match('/<title>([^<]+)<\/title>/i', $html, $matches)) {
        $title = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        error_log("[XBuilder LinkedIn] Title: $title");

        // Format: "Name - Headline | LinkedIn" or "Name | LinkedIn"
        $title = str_replace(' | LinkedIn', '', $title);
        $title = str_replace(' - LinkedIn', '', $title);

        if (strpos($title, ' - ') !== false) {
            $parts = explode(' - ', $title, 2);
            $data['name'] = trim($parts[0]);
            $data['headline'] = trim($parts[1]);
        } else {
            $data['name'] = trim($title);
        }
    }

    // Strategy 2: Open Graph meta tags
    if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $ogTitle = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        error_log("[XBuilder LinkedIn] OG Title: $ogTitle");

        if (empty($data['name'])) {
            $data['name'] = $ogTitle;
        }
    }

    if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $ogDesc = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        error_log("[XBuilder LinkedIn] OG Description: $ogDesc");

        if (empty($data['headline'])) {
            $data['headline'] = $ogDesc;
        }
    }

    // Strategy 3: Twitter Card meta tags
    if (preg_match('/<meta\s+name=["\']twitter:title["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        if (empty($data['name'])) {
            $data['name'] = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        }
    }

    if (preg_match('/<meta\s+name=["\']twitter:description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        if (empty($data['headline'])) {
            $data['headline'] = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        }
    }

    // Strategy 4: JSON-LD structured data
    if (preg_match('/<script\s+type=["\']application\/ld\+json["\']>(.+?)<\/script>/is', $html, $matches)) {
        $json = json_decode($matches[1], true);
        if ($json && is_array($json)) {
            error_log("[XBuilder LinkedIn] Found JSON-LD data");

            if (isset($json['name']) && empty($data['name'])) {
                $data['name'] = $json['name'];
            }
            if (isset($json['jobTitle']) && empty($data['headline'])) {
                $data['headline'] = $json['jobTitle'];
            }
            if (isset($json['description']) && empty($data['about'])) {
                $data['about'] = substr($json['description'], 0, 500);
            }
            if (isset($json['address']['addressLocality'])) {
                $data['location'] = $json['address']['addressLocality'];
            }
        }
    }

    // Strategy 5: Extract from meta description
    if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $desc = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        if (empty($data['about']) && strlen($desc) > 50) {
            $data['about'] = $desc;
        }
    }

    // Strategy 6: Extract username from URL as last resort
    if (empty($data['name']) && preg_match('/linkedin\.com\/in\/([^\/\?]+)/', $url, $matches)) {
        $username = str_replace('-', ' ', $matches[1]);
        $username = ucwords($username);
        $data['name'] = $username;
        error_log("[XBuilder LinkedIn] Extracted name from URL: $username");
    }

    // Clean up data
    $data['name'] = trim($data['name']);
    $data['headline'] = trim($data['headline']);
    $data['location'] = trim($data['location']);
    $data['about'] = trim($data['about']);

    return $data;
}

/**
 * Format profile data as readable text for AI
 */
function formatProfileData(array $data): string
{
    $text = "LinkedIn Profile:\n\n";

    if (!empty($data['name'])) {
        $text .= "Name: {$data['name']}\n";
    }

    if (!empty($data['headline'])) {
        $text .= "Professional Title: {$data['headline']}\n";
    }

    if (!empty($data['location'])) {
        $text .= "Location: {$data['location']}\n";
    }

    if (!empty($data['about'])) {
        $text .= "\nAbout:\n{$data['about']}\n";
    }

    if (!empty($data['url'])) {
        $text .= "\nProfile URL: {$data['url']}\n";
    }

    $text .= "\n---\n";
    $text .= "Note: This is basic information from the LinkedIn profile. ";
    $text .= "For more details, you can upload your full CV or tell me more about your experience.\n";

    return $text;
}
