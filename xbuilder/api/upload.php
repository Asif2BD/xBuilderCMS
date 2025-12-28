<?php
/**
 * XBuilder Upload API
 *
 * Handles document uploads (CV, resume, etc.) for AI processing.
 *
 * Variables available from router:
 * - $GLOBALS['xbuilder_config']: Config instance
 * - $GLOBALS['xbuilder_security']: Security instance
 */

use XBuilder\Core\Conversation;

header('Content-Type: application/json');

$config = $GLOBALS['xbuilder_config'];
$security = $GLOBALS['xbuilder_security'];

// Check authentication
if (!$security->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$security->verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

// Check for file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

// Validate upload
$allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'md', 'json'];
$maxSize = 10 * 1024 * 1024; // 10MB

$errors = $security->validateUpload($file, $allowedTypes, $maxSize);
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

try {
    // Get file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Extract text content based on file type
    $content = '';

    switch ($ext) {
        case 'txt':
        case 'md':
            $content = file_get_contents($file['tmp_name']);
            break;

        case 'json':
            $json = file_get_contents($file['tmp_name']);
            $data = json_decode($json, true);
            if ($data) {
                $content = formatJsonForAI($data);
            } else {
                $content = $json;
            }
            break;

        case 'pdf':
            $content = extractPdfText($file['tmp_name']);
            break;

        case 'docx':
            $content = extractDocxText($file['tmp_name']);
            break;

        case 'doc':
            $content = extractDocText($file['tmp_name']);
            break;

        default:
            throw new \RuntimeException('Unsupported file type');
    }

    if (empty(trim($content))) {
        throw new \RuntimeException('Could not extract text from file. Try a different format.');
    }

    // Clean up the content
    $content = cleanExtractedText($content);

    // Store in conversation
    $conversation = new Conversation();
    $conversation->setDocumentContent($content, $file['name']);

    // Save file for reference
    $storageDir = dirname(__DIR__) . '/storage';
    $uploadDir = $storageDir . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0700, true);
    }

    $savedName = date('Y-m-d_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    move_uploaded_file($file['tmp_name'], $uploadDir . $savedName);

    echo json_encode([
        'success' => true,
        'message' => 'File uploaded and processed',
        'filename' => $file['name'],
        'contentLength' => strlen($content)
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Extract text from PDF
 */
function extractPdfText(string $path): string
{
    // Try pdftotext first (most reliable)
    if (function_exists('exec')) {
        $output = [];
        $return = 0;
        exec('which pdftotext 2>/dev/null', $output, $return);

        if ($return === 0) {
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
            exec('pdftotext -layout ' . escapeshellarg($path) . ' ' . escapeshellarg($tempFile) . ' 2>/dev/null', $output, $return);

            if ($return === 0 && file_exists($tempFile)) {
                $text = file_get_contents($tempFile);
                unlink($tempFile);
                return $text;
            }
        }
    }

    // Fallback: basic PDF text extraction
    $content = file_get_contents($path);

    // Extract text between stream markers
    $text = '';
    if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches)) {
        foreach ($matches[1] as $stream) {
            // Try to decompress if FlateDecode
            $decompressed = @gzuncompress($stream);
            if ($decompressed) {
                $stream = $decompressed;
            }

            // Extract text
            if (preg_match_all('/\((.*?)\)/', $stream, $textMatches)) {
                $text .= implode(' ', $textMatches[1]) . "\n";
            }
            if (preg_match_all('/\[(.*?)\]TJ/', $stream, $tjMatches)) {
                foreach ($tjMatches[1] as $tj) {
                    if (preg_match_all('/\((.*?)\)/', $tj, $innerText)) {
                        $text .= implode('', $innerText[1]);
                    }
                }
                $text .= "\n";
            }
        }
    }

    if (empty(trim($text))) {
        throw new \RuntimeException('Could not extract text from PDF. Please try pdftotext or upload as DOCX/TXT.');
    }

    return $text;
}

/**
 * Extract text from DOCX
 */
function extractDocxText(string $path): string
{
    $zip = new \ZipArchive();

    if ($zip->open($path) !== true) {
        throw new \RuntimeException('Could not open DOCX file');
    }

    // Read the main document
    $content = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($content === false) {
        throw new \RuntimeException('Could not read DOCX content');
    }

    // Strip XML tags but preserve structure
    $content = preg_replace('/<w:p[^>]*>/', "\n", $content);
    $content = preg_replace('/<w:tab[^>]*>/', "\t", $content);
    $content = strip_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return $content;
}

/**
 * Extract text from DOC (old format)
 */
function extractDocText(string $path): string
{
    // Try antiword first
    if (function_exists('exec')) {
        $output = [];
        $return = 0;
        exec('which antiword 2>/dev/null', $output, $return);

        if ($return === 0) {
            exec('antiword ' . escapeshellarg($path) . ' 2>/dev/null', $output, $return);
            if ($return === 0) {
                return implode("\n", $output);
            }
        }
    }

    // Fallback: extract ASCII text
    $content = file_get_contents($path);

    // Filter to printable ASCII and common characters
    $text = '';
    $buffer = '';
    $inText = false;

    for ($i = 0; $i < strlen($content); $i++) {
        $char = $content[$i];
        $ord = ord($char);

        // Printable ASCII range
        if ($ord >= 32 && $ord <= 126) {
            $buffer .= $char;
            $inText = true;
        } elseif ($ord === 10 || $ord === 13) {
            if ($inText && strlen($buffer) > 3) {
                $text .= $buffer . "\n";
            }
            $buffer = '';
            $inText = false;
        } else {
            if ($inText && strlen($buffer) > 3) {
                $text .= $buffer . ' ';
            }
            $buffer = '';
            $inText = false;
        }
    }

    if (strlen($buffer) > 3) {
        $text .= $buffer;
    }

    if (empty(trim($text))) {
        throw new \RuntimeException('Could not extract text from DOC. Please install antiword or upload as DOCX/TXT.');
    }

    return $text;
}

/**
 * Format JSON data for AI consumption
 */
function formatJsonForAI(array $data, int $depth = 0): string
{
    $output = '';
    $indent = str_repeat('  ', $depth);

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $output .= $indent . ucfirst(str_replace('_', ' ', $key)) . ":\n";
            $output .= formatJsonForAI($value, $depth + 1);
        } else {
            $output .= $indent . ucfirst(str_replace('_', ' ', $key)) . ': ' . $value . "\n";
        }
    }

    return $output;
}

/**
 * Clean up extracted text
 */
function cleanExtractedText(string $text): string
{
    // Remove excessive whitespace
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);

    // Remove non-printable characters except newlines and tabs
    $text = preg_replace('/[^\x20-\x7E\x0A\x0D\t]/', '', $text);

    // Trim lines
    $lines = explode("\n", $text);
    $lines = array_map('trim', $lines);
    $text = implode("\n", $lines);

    return trim($text);
}
