<?php

namespace App\Helpers;

use NumberToWords\NumberToWords;

class NumberToWordsHelper
{
    /**
     * Convert number to words.
     *
     * @param float $number
     * @param string $currency (optional)
     * @param string $subunit (optional)
     * @return string
     */
    public static function convert($number, $currency = 'Taka', $subunit = 'Paisa')
    {
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        $intPart = floor($number);
        $decimalPart = round(($number - $intPart) * 100);

        $words = $numberTransformer->toWords($intPart) . " " . $currency;

        if ($decimalPart > 0) {
            $words .= " and " . $numberTransformer->toWords($decimalPart) . " " . $subunit;
        }

        return ucfirst($words) . " only";
    }
}
