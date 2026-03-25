<?php

namespace App\Services;

use App\Repositories\Interfaces\CurrencyRepositoryInterface;

class CurrencyService
{
    public function __construct(
        protected CurrencyRepositoryInterface $currencyRepo
    ) {}

    public function format($amount, $currencyId = null)
    {
        $currency = $currencyId 
            ? $this->currencyRepo->find($currencyId) 
            : $this->currencyRepo->getDefault();

        if (!$currency) {
            return '$' . number_format($amount, 2);
        }

        $formatted = number_format($amount, $currency->decimal_places);
        
        return $currency->symbol_position === 'prefix' 
            ? $currency->symbol . $formatted 
            : $formatted . ' ' . $currency->symbol;
    }
}
