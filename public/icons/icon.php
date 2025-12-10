<?php
// Dynamic PNG icon generator for UniMind PWA
// Usage: icon.php?size=192

// Validate size
$size = isset($_GET['size']) ? (int) $_GET['size'] : 192;
if ($size <= 0) $size = 192;
if ($size > 1024) $size = 1024;

// Colors
$bgColor = [74, 144, 226]; // #4a90e2
$textColor = [255, 255, 255];

// Create image
$im = imagecreatetruecolor($size, $size);
$bg = imagecolorallocate($im, $bgColor[0], $bgColor[1], $bgColor[2]);
$tc = imagecolorallocate($im, $textColor[0], $textColor[1], $textColor[2]);
imagefilledrectangle($im, 0, 0, $size, $size, $bg);

// Add rounded corner mask (approximation)
$radius = (int) ($size * 0.08);
// Antialiasing for better quality when available
if (function_exists('imagesavealpha')) {
    imagesavealpha($im, true);
}

// Draw text: 'UM'
$fontFile = __DIR__ . '/../../public/fonts/'; // try to find a TTF nearby
$text = 'UM';

// Choose font if available (search common places)
$possibleFonts = [
    __DIR__ . '/../../public/fonts/Inter-Regular.ttf',
    __DIR__ . '/../../public/fonts/Poppins-Regular.ttf',
    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'
];
$font = null;
foreach ($possibleFonts as $f) {
    if (file_exists($f)) { $font = $f; break; }
}

if ($font) {
    // Calculate font size to fit
    $fontSize = max(12, (int) ($size * 0.48));
    $bbox = imagettfbbox($fontSize, 0, $font, $text);
    $textWidth = $bbox[2] - $bbox[0];
    $textHeight = $bbox[1] - $bbox[7];
    $x = ($size - $textWidth) / 2 - $bbox[0];
    $y = ($size + $textHeight) / 2;
    imagettftext($im, $fontSize, 0, (int)$x, (int)$y, $tc, $font, $text);
} else {
    // Fallback: use built-in font
    $fontSize = (int) ($size / 4);
    $x = (int) ($size * 0.28);
    $y = (int) ($size * 0.6);
    imagestring($im, 5, $x, $y - ($fontSize/2), $text, $tc);
}

// Output headers
header('Content-Type: image/png');
header('Cache-Control: public, max-age=604800'); // 1 week

imagepng($im);
imagedestroy($im);

exit;

?>
