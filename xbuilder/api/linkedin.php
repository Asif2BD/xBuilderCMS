<?php
/**
 * XBuilder LinkedIn Profile Fetcher
 *
 * Fetches and parses public LinkedIn profiles without requiring API access
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

// Validate LinkedIn URL
if (!preg_match('/linkedin\.com\/(in|pub)\//', $linkedinUrl)) {
    echo json_encode(['success' => false, 'error' => 'Invalid LinkedIn profile URL. Must be a linkedin.com/in/... URL']);
    exit;
}

try {
    // Fetch the LinkedIn profile page
    $html = fetchLinkedInProfile($linkedinUrl);

    if (!$html) {
        echo json_encode(['success' => false, 'error' => 'Could not fetch LinkedIn profile. The profile may be private or the URL is invalid.']);
        exit;
    }

    // Parse profile data
    $profileData = parseLinkedInProfile($html, $linkedinUrl);

    if (empty($profileData['name']) && empty($profileData['headline'])) {
        echo json_encode(['success' => false, 'error' => 'Could not extract profile data. The profile may be private or require login.']);
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
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch LinkedIn profile: ' . $e->getMessage()
    ]);
}

/**
 * Fetch LinkedIn profile HTML
 */
function fetchLinkedInProfile(string $url): ?string
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1'
        ],
        CURLOPT_ENCODING => 'gzip'
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$html) {
        return null;
    }

    return $html;
}

/**
 * Parse LinkedIn profile HTML
 */
function parseLinkedInProfile(string $html, string $url): array
{
    $data = [
        'name' => '',
        'headline' => '',
        'location' => '',
        'about' => '',
        'experience' => [],
        'education' => [],
        'skills' => [],
        'url' => $url
    ];

    // LinkedIn embeds JSON-LD structured data
    if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
        $jsonData = json_decode($matches[1], true);

        if ($jsonData && isset($jsonData['@type']) && $jsonData['@type'] === 'Person') {
            $data['name'] = $jsonData['name'] ?? '';
            $data['headline'] = $jsonData['jobTitle'] ?? '';
            $data['location'] = $jsonData['address']['addressLocality'] ?? '';
        }
    }

    // Parse meta tags (Open Graph and Twitter Card)
    if (preg_match('/<meta property="og:title" content="([^"]+)"/', $html, $matches)) {
        if (empty($data['name'])) {
            // og:title format: "Name - Headline" or "Name | LinkedIn"
            $title = html_entity_decode($matches[1]);
            $parts = preg_split('/[\-|]/', $title, 2);
            $data['name'] = trim($parts[0]);
            if (isset($parts[1]) && !str_contains($parts[1], 'LinkedIn')) {
                $data['headline'] = trim($parts[1]);
            }
        }
    }

    if (preg_match('/<meta property="og:description" content="([^"]+)"/', $html, $matches)) {
        if (empty($data['headline'])) {
            $data['headline'] = html_entity_decode($matches[1]);
        }
    }

    // Try to extract from title tag
    if (preg_match('/<title>([^<]+)<\/title>/', $html, $matches)) {
        if (empty($data['name'])) {
            $title = html_entity_decode($matches[1]);
            // LinkedIn title format: "Name - Headline | LinkedIn"
            $title = str_replace(' | LinkedIn', '', $title);
            $parts = explode(' - ', $title, 2);
            $data['name'] = trim($parts[0]);
            if (isset($parts[1])) {
                $data['headline'] = trim($parts[1]);
            }
        }
    }

    // Extract username from URL for fallback
    if (empty($data['name']) && preg_match('/linkedin\.com\/in\/([^\/\?]+)/', $url, $matches)) {
        $username = str_replace('-', ' ', $matches[1]);
        $username = ucwords($username);
        $data['name'] = $username;
    }

    return $data;
}

/**
 * Format profile data as readable text
 */
function formatProfileData(array $data): string
{
    $text = "LinkedIn Profile Information:\n\n";

    if (!empty($data['name'])) {
        $text .= "Name: {$data['name']}\n";
    }

    if (!empty($data['headline'])) {
        $text .= "Professional Headline: {$data['headline']}\n";
    }

    if (!empty($data['location'])) {
        $text .= "Location: {$data['location']}\n";
    }

    if (!empty($data['url'])) {
        $text .= "LinkedIn URL: {$data['url']}\n";
    }

    if (!empty($data['about'])) {
        $text .= "\nAbout:\n{$data['about']}\n";
    }

    $text .= "\n---\n\n";
    $text .= "Note: For a complete website, I'll need more details about your experience, skills, and projects. ";
    $text .= "You can provide this by uploading your CV or sharing more information through our conversation.\n";

    return $text;
}
