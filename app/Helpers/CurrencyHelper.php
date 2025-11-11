<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format amount as Philippine Peso currency
     * 
     * @param float|int|string $amount
     * @return string
     */
    public static function format($amount): string
    {
        return '₱' . number_format((float)$amount, 2);
    }
    
    /**
     * Format large amounts with K/M suffix
     * 
     * @param float|int|string $amount
     * @return string
     */
    public static function formatShort($amount): string
    {
        $num = (float)$amount;
        
        if ($num >= 1000000) {
            return '₱' . number_format($num / 1000000, 1) . 'M';
        }
        
        if ($num >= 1000) {
            return '₱' . number_format($num / 1000, 1) . 'K';
        }
        
        return '₱' . number_format($num, 2);
    }
}
