<?php
/**
 * Script to load NikoshBAN font into DomPDF
 * Run this script once: php load_nikosh_font.php
 */

require __DIR__ . '/vendor/autoload.php';

use Dompdf\FontMetrics;
use Dompdf\Options;

// Check multiple locations for the font file
$fontPaths = [
    __DIR__ . '/assets/fonts/NikoshBAN.ttf',
    __DIR__ . '/public/assets/fonts/NikoshBAN.ttf',
    __DIR__ . '/storage/fonts/nikoshban.ttf',
];

$fontFile = null;
foreach ($fontPaths as $path) {
    if (file_exists($path)) {
        $fontFile = $path;
        break;
    }
}

$fontDir = __DIR__ . '/storage/fonts';
$targetFontFile = $fontDir . '/nikoshban.ttf';

// Copy font to storage/fonts if it's not already there
if ($fontFile && $fontFile !== $targetFontFile) {
    if (!is_dir($fontDir)) {
        mkdir($fontDir, 0755, true);
    }
    copy($fontFile, $targetFontFile);
    echo "Font copied from {$fontFile} to {$targetFontFile}\n";
    $fontFile = $targetFontFile;
}

if (!$fontFile || !file_exists($fontFile)) {
    echo "Error: Font file not found!\n";
    echo "Searched in:\n";
    foreach ($fontPaths as $path) {
        echo "  - {$path}\n";
    }
    echo "Please ensure the font file exists in one of these locations.\n";
    exit(1);
}

echo "Loading NikoshBAN font into DomPDF...\n";

try {
    $options = new Options();
    $options->set('fontDir', $fontDir);
    $options->set('fontCache', $fontDir);
    
    // Create a temporary DomPDF instance to access FontMetrics
    $dompdf = new \Dompdf\Dompdf($options);
    $fontMetrics = $dompdf->getFontMetrics();
    
    // Register the font
    $fontMetrics->registerFont(
        ['family' => 'nikoshban', 'style' => 'normal', 'weight' => 'normal'],
        $fontFile
    );
    
    echo "Font loaded successfully!\n";
    echo "You can now use 'nikoshban' as the font-family in your PDF views.\n";
    
} catch (\Exception $e) {
    echo "Error loading font: " . $e->getMessage() . "\n";
    echo "\nAlternative: You may need to use DomPDF's load_font.php script if available.\n";
    exit(1);
}
