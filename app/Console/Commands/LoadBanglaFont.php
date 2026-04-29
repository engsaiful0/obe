<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dompdf\Dompdf;
use Dompdf\Options;

class LoadBanglaFont extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'font:load-bangla {font?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load Bangla font (Kalpurush, SolaimanLipi, or NikoshBAN) into DomPDF';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fontName = $this->argument('font') ?? 'kalpurush';
        
        // Check multiple locations for font file
        $fontPaths = [
            storage_path("fonts/{$fontName}.ttf"),
            base_path("assets/fonts/{$fontName}.ttf"),
            public_path("assets/fonts/{$fontName}.ttf"),
        ];
        
        // Special handling for NikoshBAN
        if (strtolower($fontName) === 'nikoshban') {
            $fontPaths = array_merge([
                base_path('assets/fonts/NikoshBAN.ttf'),
                public_path('assets/fonts/NikoshBAN.ttf'),
                storage_path('fonts/NikoshBAN.ttf'),
            ], $fontPaths);
        }
        
        $fontFile = null;
        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                $fontFile = $path;
                break;
            }
        }
        
        if (!$fontFile) {
            $this->error("Font file not found for: {$fontName}");
            $this->info("Please ensure the font file is in one of these locations:");
            $this->info("  - storage/fonts/{$fontName}.ttf");
            $this->info("  - assets/fonts/{$fontName}.ttf");
            $this->info("  - public/assets/fonts/{$fontName}.ttf");
            return 1;
        }
        
        $this->info("Found font file: {$fontFile}");
        
        // Copy font to storage/fonts with lowercase name (DomPDF requirement)
        $fontDir = storage_path('fonts');
        $targetName = strtolower($fontName);
        $targetFile = $fontDir . '/' . $targetName . '.ttf';
        
        if ($fontFile !== $targetFile) {
            if (copy($fontFile, $targetFile)) {
                $this->info("Font copied to: {$targetFile}");
            } else {
                $this->error("Failed to copy font file");
                return 1;
            }
        }
        
        // Try to load font using DomPDF's load_font.php if available
        $loadScript = base_path('vendor/dompdf/dompdf/load_font.php');
        if (file_exists($loadScript)) {
            $this->info("Loading font into DomPDF...");
            $command = sprintf(
                'php %s %s %s %s',
                escapeshellarg($loadScript),
                escapeshellarg($targetName),
                escapeshellarg($targetFile),
                escapeshellarg($fontDir)
            );
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $this->info("Font loaded successfully!");
            } else {
                $this->warn("Font loading script returned code: {$returnCode}");
                $this->info("Font file is in place, but metrics generation may have failed.");
            }
        } else {
            $this->warn("DomPDF load_font.php script not found. Font file copied but may need manual loading.");
            $this->info("You may need to run: php vendor/dompdf/dompdf/load_font.php {$targetName} {$targetFile} {$fontDir}");
        }
        
        $this->info("Font setup complete! You can now use '{$targetName}' as the font-family in your PDF views.");
        
        return 0;
    }
}
