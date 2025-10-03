<?php
/**
 * SmartApply Cover Letter Analysis API (Gemini) — Final Stable JSON Version
 * - Primary: v1 + gemini-2.5-flash
 * - Fallback: v1beta + gemini-1.5-flash (only if primary returns 404)
 * - Minimal v1 payload (single user message; no systemInstruction field)
 * - Global error/exception handlers -> always JSON (no HTML pages)
 * - Strong JSON cleanup/extraction from model output
 * - Optional rate limiting respected by DISABLE_RATE_LIMITING
 */

define('DISABLE_RATE_LIMITING', true);

require_once '../config/config.php';
require_once '../config/database.php';

/* ---------- Always-JSON error handling ---------- */
header('Content-Type: application/json');
set_exception_handler(function ($e) {
    error_log('Uncaught exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function ($severity, $message, $file, $line) {
    // Convert all PHP errors/warnings/notices to exceptions so we return JSON
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/* ---------- CORS for Chrome extensions & web ---------- */
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && strpos($origin, 'chrome-extension://') === 0) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

/* ---------- DB connection ---------- */
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable']);
    exit;
}

/* ---------- Input ---------- */
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);

if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$coverLetter    = trim($input['coverLetter']    ?? '');
$jobTitle       = trim($input['jobTitle']       ?? '');
$jobDescription = trim($input['jobDescription'] ?? '');
$licenseKey     = trim($input['licenseKey']     ?? '');

if ($coverLetter === '' || $jobTitle === '' || $jobDescription === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}
if ($licenseKey === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'License key is required']);
    exit;
}
if (strlen($coverLetter) > 5000 || strlen($jobTitle) > 200 || strlen($jobDescription) > 5000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input exceeds maximum length']);
    exit;
}

/* ---------- License check ---------- */
try {
    $stmt = $pdo->prepare("SELECT status FROM licenses WHERE license_key = ? AND status = 'active'");
    $stmt->execute([$licenseKey]);
    $license = $stmt->fetch();
    if (!$license) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired license key']);
        exit;
    }
} catch (PDOException $e) {
    error_log("License check failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable']);
    exit;
}

/* ---------- Gemini API key ---------- */
try {
    $stmt = $pdo->query("SELECT api_key FROM api_keys WHERE service = 'gemini' AND status = 'active' LIMIT 1");
    $apiKeyData = $stmt->fetch();
    if (!$apiKeyData) {
        error_log("Gemini API key not found");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Service configuration error']);
        exit;
    }
    $geminiApiKey = $apiKeyData['api_key'];
} catch (Exception $e) {
    error_log("Failed to retrieve API key: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable']);
    exit;
}

/* ---------- Optional rate limiting ---------- */
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!DISABLE_RATE_LIMITING) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS request_count
            FROM api_usage_log
            WHERE license_key = ?
              AND endpoint = 'cover-letter-analysis'
              AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute([$licenseKey]);
        $count = (int)($stmt->fetch()['request_count'] ?? 0);
        if ($count >= 10) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Rate limit exceeded. Please try again later.']);
            exit;
        }
    } catch (Exception $e) {
        error_log("Rate limit check failed: " . $e->getMessage());
    }
}

/* ---------- Usage log ---------- */
try {
    $stmt = $pdo->prepare("
        INSERT INTO api_usage_log (ip_address, license_key, endpoint, created_at)
        VALUES (?, ?, 'cover-letter-analysis', NOW())
    ");
    $stmt->execute([$clientIp, $licenseKey]);
} catch (Exception $e) {
    error_log("Failed to log API usage: " . $e->getMessage());
}

/* ---------- Build Gemini request ---------- */
$systemPrompt = <<<EOT
You are an expert cover letter analyst. Your ONLY job is to output a valid JSON object that matches the schema below.

RULES:
- Output raw JSON only. No explanations, no prose, no markdown or code fences.
- Keep feedback concise and actionable.

SCHEMA:
{
  "grammarScore": <number 0-10>,
  "grammarFeedback": "<brief feedback>",
  "sentenceLengthScore": <number 0-10>,
  "sentenceLengthFeedback": "<brief feedback>",
  "toneScore": <number 0-10>,
  "toneFeedback": "<brief feedback>",
  "hookScore": <number 0-10>,
  "hookFeedback": "<brief feedback>",
  "overallFeedback": "<overall assessment>",
  "recommendations": ["<rec1>", "<rec2>", "<rec3>"]
}
EOT;

$userPrompt =
    "Job Title: {$jobTitle}\n\n" .
    "Job Description:\n{$jobDescription}\n\n" .
    "Cover Letter:\n{$coverLetter}\n\n" .
    "Provide detailed scoring and feedback. Output raw JSON ONLY matching the schema.";

/* ---------- Minimal, v1-compatible payload ---------- */
$payload = [
    'contents' => [[
        'role'  => 'user',
        'parts' => [[ 'text' => $systemPrompt . "\n\n" . $userPrompt ]]
    ]],
    'generationConfig' => [
        'temperature'     => 0.3,
        'maxOutputTokens' => 1500,
        'topP'            => 0.8,
        'topK'            => 40
    ]
];

$primary  = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}";
$fallback = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiApiKey}";

/* ---------- HTTP helper with retry ---------- */
function httpPostJsonWithRetry($url, $payload, $maxRetries = 3) {
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("Gemini curl error (try $attempt): $err");
            if ($attempt < $maxRetries) { sleep(pow(2, $attempt)); continue; }
            return ['ok' => false, 'code' => 0, 'body' => null];
        }
        if ($code === 200) return ['ok' => true, 'code' => 200, 'body' => $response];
        if ($code === 400) return ['ok' => false, 'code' => 400, 'body' => $response];
        if ($code === 429) { if ($attempt < $maxRetries) { sleep(pow(2, $attempt)*3); continue; } return ['ok' => false, 'code' => 429, 'body' => $response]; }
        if ($code >= 500 && $attempt < $maxRetries) { sleep(pow(2, $attempt)); continue; }
        return ['ok' => false, 'code' => $code, 'body' => $response];
    }
    return ['ok' => false, 'code' => 0, 'body' => null];
}

/* ---------- Call Gemini: primary then fallback(404) ---------- */
$apiResult = httpPostJsonWithRetry($primary, $payload, 3);
if (!$apiResult['ok'] && $apiResult['code'] === 404) {
    error_log("Primary 404. Trying fallback v1beta + gemini-1.5-flash...");
    $apiResult = httpPostJsonWithRetry($fallback, $payload, 3);
}

/* ---------- Handle non-OK ---------- */
if (!$apiResult['ok']) {
    $code = $apiResult['code'] ?? 500;

    if ($code === 400) {
        http_response_code(400);
        $detail = null;
        if (!empty($apiResult['body'])) {
            error_log("Gemini 400 body: " . $apiResult['body']);
            $parsed = json_decode($apiResult['body'], true);
            if (isset($parsed['error']['message'])) {
                $detail = $parsed['error']['message'];
            } elseif (isset($parsed['error'])) {
                $detail = json_encode($parsed['error']);
            } else {
                $detail = $apiResult['body'];
            }
        }
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request format.',
            'detail'  => $detail
        ]);
        exit;
    }

    if ($code === 429) {
        http_response_code(429);
        echo json_encode(['success'=>false,'message'=>'Service temporarily busy. Please try again in a moment.']);
        exit;
    }
    if ($code === 404) {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'Analysis service configuration error.']);
        exit;
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Analysis service temporarily unavailable']);
    exit;
}

/* ---------- Parse Gemini response ---------- */
$gemini = json_decode($apiResult['body'], true);
if (!$gemini) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Invalid response from analysis service']);
    exit;
}
if (isset($gemini['error'])) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Analysis service error: '.($gemini['error']['message'] ?? 'Unknown')]);
    exit;
}

/* Extract model text */
$analysisText = $gemini['candidates'][0]['content']['parts'][0]['text'] ?? '';
if ($analysisText === '' && isset($gemini['candidates'][0]['content']['parts'])) {
    $parts = $gemini['candidates'][0]['content']['parts'];
    $analysisText = trim(implode("\n", array_map(function ($p) { return $p['text'] ?? ''; }, $parts)));
}
if ($analysisText === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unexpected response format from analysis service']);
    exit;
}

/* ---------- Clean and parse model JSON ---------- */
/* Strip any code fences (``` or ```json) and whitespace */
$analysisText = preg_replace('/```(?:json)?/i', '', $analysisText);
$analysisText = str_replace('```', '', $analysisText);
$analysisText = trim($analysisText);

/* Try direct decode first */
$analysis = json_decode($analysisText, true);

/* If that failed, try to extract the first balanced JSON object with a recursive regex */
if (!$analysis) {
    if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $analysisText, $m)) {
        $analysis = json_decode($m[0], true);
    }
}

if (!$analysis || !is_array($analysis)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to parse analysis results',
        'raw'     => $analysisText  // helpful for debugging if needed
    ]);
    exit;
}

/* ---------- Normalize data ---------- */
$analysis['grammarScore']        = max(0, min(10, intval($analysis['grammarScore'] ?? 0)));
$analysis['sentenceLengthScore'] = max(0, min(10, intval($analysis['sentenceLengthScore'] ?? 0)));
$analysis['toneScore']           = max(0, min(10, intval($analysis['toneScore'] ?? 0)));
$analysis['hookScore']           = max(0, min(10, intval($analysis['hookScore'] ?? 0)));

$analysis['grammarFeedback']         = $analysis['grammarFeedback']         ?? 'No feedback provided';
$analysis['sentenceLengthFeedback']  = $analysis['sentenceLengthFeedback']  ?? 'No feedback provided';
$analysis['toneFeedback']            = $analysis['toneFeedback']            ?? 'No feedback provided';
$analysis['hookFeedback']            = $analysis['hookFeedback']            ?? 'No feedback provided';
$analysis['overallFeedback']         = $analysis['overallFeedback']         ?? 'Analysis completed';

if (!isset($analysis['recommendations']) || !is_array($analysis['recommendations']) || count($analysis['recommendations']) === 0) {
    $analysis['recommendations'] = [
        'Consider reviewing your cover letter',
        'Ensure it matches the job requirements',
        'Proofread for any errors'
    ];
}

/* ---------- Log summary ---------- */
try {
    $stmt = $pdo->prepare("
        INSERT INTO cover_letter_analysis_log (ip_address, license_key, total_score, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $totalScore = $analysis['grammarScore'] + $analysis['sentenceLengthScore'] + $analysis['toneScore'] + $analysis['hookScore'];
    $stmt->execute([$clientIp, $licenseKey, $totalScore]);
} catch (Exception $e) {
    error_log("Failed to log analysis: " . $e->getMessage());
}

/* ---------- Respond ---------- */
echo json_encode([
    'success' => true,
    'message' => 'Analysis completed successfully',
    'data'    => $analysis
]);
