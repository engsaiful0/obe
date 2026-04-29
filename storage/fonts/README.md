# Bangla Font Support for PDF Export

To enable Bangla (Bengali) font support in PDF exports, please follow these steps:

## Step 1: Download a Bangla Font

Download one of the following Bangla Unicode fonts:
- **Kalpurush** (Recommended): https://www.omicronlab.com/bangla-fonts/kalpurush.html
- **SolaimanLipi**: https://www.omicronlab.com/bangla-fonts/solaimanlipi.html

## Step 2: Place Font File

1. Download the `.ttf` font file
2. Rename it to lowercase:
   - `kalpurush.ttf` OR
   - `solaimanlipi.ttf`
3. Place the font file in this directory: `storage/fonts/`

## Step 3: Load the Font

Run the following Artisan command to load the font into DomPDF:

```bash
php artisan font:load-bangla kalpurush
```

Or for SolaimanLipi:

```bash
php artisan font:load-bangla solaimanlipi
```

## Step 4: Verify

After loading the font, the PDF export will automatically use it for displaying Bangla text.

## Important Notes

- The font file must be in TrueType format (.ttf)
- The font name in the file should be lowercase (kalpurush.ttf or solaimanlipi.ttf)
- You must run the `font:load-bangla` command after placing the font file
- If no Bangla font is found, the system will fall back to DejaVu Sans (which has limited Bangla support)

## Troubleshooting

If Bangla text still doesn't display correctly:
1. Make sure the font file is in `storage/fonts/` directory
2. Run `php artisan font:load-bangla [font-name]` command
3. Clear your application cache: `php artisan cache:clear`
4. Try regenerating the PDF

