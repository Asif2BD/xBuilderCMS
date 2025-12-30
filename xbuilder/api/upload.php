<?php
/**
 * XBuilder Upload API
 * 
 * Handles file uploads (CV, documents, etc.)
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/core/Security.php';

use XBuilder\Core\Security;

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check for uploaded file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['file']['error'] ?? 'No file uploaded';
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Upload failed: ' . $error]);
    exit;
}

$file = $_FILES['file'];
$filename = $file['name'];
$tmpPath = $file['tmp_name'];
$size = $file['size'];

// Validate file size (max 10MB)
if ($size > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum 10MB allowed.']);
    exit;
}

// Get file extension
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Allowed extensions
$allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'json', 'md'];

if (!in_array($ext, $allowedExtensions)) {
    echo json_encode(['success' => false, 'error' => 'File type not supported. Allowed: PDF, DOC, DOCX, TXT, JSON, MD']);
    exit;
}

// Extract text content based on file type
$content = '';

try {
    switch ($ext) {
        case 'txt':
        case 'md':
        case 'json':
            // Plain text files
            $content = file_get_contents($tmpPath);
            break;
            
        case 'pdf':
            // Try to extract text from PDF
            $content = extractPdfText($tmpPath);
            break;
            
        case 'doc':
        case 'docx':
            // Try to extract text from Word documents
            $content = extractWordText($tmpPath, $ext);
            break;
            
        default:
            $content = file_get_contents($tmpPath);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to read file: ' . $e->getMessage()]);
    exit;
}

if (empty(trim($content))) {
    echo json_encode(['success' => false, 'error' => 'Could not extract text from file. Try a different format.']);
    exit;
}

// Limit content length (for API calls)
$maxLength = 50000; // ~50k characters
if (strlen($content) > $maxLength) {
    $content = substr($content, 0, $maxLength) . "\n\n[Content truncated...]";
}

// Clean up the content
$content = cleanText($content);

// Save to uploads folder for reference
$uploadsDir = dirname(__DIR__) . '/storage/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0700, true);
}

$savedPath = $uploadsDir . '/' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
move_uploaded_file($tmpPath, $savedPath);

// Log the extraction for debugging
error_log("[XBuilder Upload] File: {$filename}, Size: {$size} bytes, Extracted: " . strlen($content) . " chars");

echo json_encode([
    'success' => true,
    'filename' => $filename,
    'content' => $content,
    'length' => strlen($content),
    'preview' => substr($content, 0, 200) . '...'
]);

/**
 * Extract text from PDF using pdftotext command line tool
 */
function extractPdfText(string $path): string
{
    // Check if pdftotext is available
    $pdftotext = trim(shell_exec('which pdftotext 2>/dev/null'));

    if ($pdftotext) {
        // Use pdftotext with UTF-8 encoding
        // Output to stdout and capture with shell_exec (more reliable than exec)
        $text = shell_exec("pdftotext -enc UTF-8 -layout " . escapeshellarg($path) . " - 2>&1");

        // Check if we got actual text (not error messages)
        if ($text && strlen($text) > 50 && !str_contains($text, 'Error') && !str_contains($text, 'Syntax Error')) {
            error_log("[XBuilder PDF] Extracted " . strlen($text) . " chars using pdftotext");
            return $text;
        }

        // Try without -layout flag (some PDFs work better)
        $text = shell_exec("pdftotext -enc UTF-8 " . escapeshellarg($path) . " - 2>&1");

        if ($text && strlen($text) > 50 && !str_contains($text, 'Error') && !str_contains($text, 'Syntax Error')) {
            error_log("[XBuilder PDF] Extracted " . strlen($text) . " chars using pdftotext (no layout)");
            return $text;
        }

        error_log("[XBuilder PDF] pdftotext failed or returned too little text");
    }

    // Fallback: Try to read raw PDF content
    error_log("[XBuilder PDF] Attempting fallback extraction");
    $content = file_get_contents($path);

    // Very basic PDF text extraction (works for some uncompressed PDFs)
    $text = '';

    // Find text between stream and endstream
    preg_match_all('/stream(.*?)endstream/s', $content, $matches);

    foreach ($matches[1] as $match) {
        // Try to decode if it's compressed with FlateDecode
        $decoded = @gzuncompress($match);
        if ($decoded) {
            $match = $decoded;
        }

        // Extract text objects between parentheses
        preg_match_all('/\((.*?)\)/s', $match, $textMatches);
        foreach ($textMatches[1] as $textMatch) {
            // Skip if it looks like binary data
            if (preg_match('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', $textMatch)) {
                continue;
            }
            $text .= $textMatch . ' ';
        }
    }

    $text = trim($text);

    // If we got some text, return it
    if (!empty($text) && strlen($text) > 30) {
        error_log("[XBuilder PDF] Extracted " . strlen($text) . " chars using fallback method");
        return $text;
    }

    // If all else fails, suggest an alternative
    error_log("[XBuilder PDF] Extraction failed completely");
    throw new Exception('Could not extract text from this PDF. It may be encrypted, scanned, or have special encoding. Please try uploading a DOCX or TXT file instead.');
}

/**
 * Extract text from Word documents
 */
function extractWordText(string $path, string $ext): string
{
    if ($ext === 'docx') {
        // DOCX is a ZIP file containing XML
        $zip = new ZipArchive();
        
        if ($zip->open($path) === true) {
            // Read the main document content
            $content = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if ($content) {
                // Strip XML tags to get plain text
                $text = strip_tags($content);
                // Clean up whitespace
                $text = preg_replace('/\s+/', ' ', $text);
                return trim($text);
            }
        }
        
        throw new Exception('Could not read DOCX file');
    }
    
    if ($ext === 'doc') {
        // Old .doc format is more complex
        // Try antiword if available
        $antiword = trim(shell_exec('which antiword 2>/dev/null'));
        
        if ($antiword) {
            $output = shell_exec("antiword " . escapeshellarg($path) . " 2>/dev/null");
            if (!empty($output)) {
                return $output;
            }
        }
        
        // Fallback: try to extract plain text
        $content = file_get_contents($path);
        
        // Extract readable ASCII text
        $text = '';
        preg_match_all('/[\x20-\x7E]{4,}/', $content, $matches);
        
        if (!empty($matches[0])) {
            $text = implode(' ', $matches[0]);
        }
        
        if (!empty(trim($text))) {
            return trim($text);
        }
        
        throw new Exception('DOC text extraction not available. Please try uploading a DOCX or TXT file.');
    }
    
    throw new Exception('Unsupported file format');
}

/**
 * Clean up extracted text
 */
function cleanText(string $text): string
{
    // Remove excessive whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Remove non-printable characters (except newlines)
    $text = preg_replace('/[^\x20-\x7E\n\r]/', '', $text);
    
    // Normalize line endings
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    
    // Remove excessive newlines
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    
    return trim($text);
}
