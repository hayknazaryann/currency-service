<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    /**
     * @param CurrencyService $currencyService
     */
    public function __construct(
        protected CurrencyService $currencyService
    )
    {
    }

    /**
     * @return JsonResponse
     */
    public function getCurrencyDailyRates(): JsonResponse
    {
        return $this->currencyService->cbrDailyRates();
    }
}
