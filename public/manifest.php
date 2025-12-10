<?php
// Dynamic manifest generator to ensure start_url / scope match deployment base
header('Content-Type: application/manifest+json; charset=utf-8');

$script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base = $script;
// If the script path contains '/public', strip it to get the app base
if (strpos($script, '/public') !== false) {
    $base = substr($script, 0, strpos($script, '/public'));
}

// Normalize base and ensure trailing slash when used as start_url/scope
if ($base === '.' || $base === '/') {
    $base = '';
}
$base_with_slash = $base === '' ? '/' : $base . '/';

$manifest_path = __DIR__ . '/manifest.webmanifest';
if (!file_exists($manifest_path)) {
    http_response_code(404);
    echo json_encode(['error' => 'manifest.webmanifest not found']);
    exit;
}

$raw = file_get_contents($manifest_path);
$data = json_decode($raw, true);
if (!is_array($data)) {
    // If JSON invalid, just output raw
    echo $raw;
    exit;
}

// Override start_url and scope to match detected base
$data['start_url'] = $base_with_slash;
$data['scope'] = $base_with_slash;

// Rewrite icons to absolute URLs based on detected base so they always resolve
if (isset($data['icons']) && is_array($data['icons'])) {
    foreach ($data['icons'] as &$icon) {
        $src = $icon['src'] ?? '';
        // If already absolute (starts with http or //) leave as-is
        if (preg_match('#^https?://#i', $src) || strpos($src, '//') === 0) {
            continue;
        }
        // If starts with '/', it's root absolute. Prefix base if it doesn't already include it.
        if (strpos($src, '/') === 0) {
            if ($base !== '' && strpos($src, $base) !== 0) {
                $icon['src'] = $base . $src;
            }
            continue;
        }
        // Relative path: assume location under /public/, so base + '/public/' + src
        $icon['src'] = $base . '/public/' . ltrim($src, './');
    }
    unset($icon);
}

echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

?>
