<?php

namespace App\Services;

use App\Http\Resources\CurrencyResource;
use App\Repositories\Interfaces\CurrencyInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CurrencyService
{
    /**
     * @param RequestService $requestService
     */
    public function __construct(
        protected RequestService $requestService,
        protected CurrencyInterface $currencyRepository
    )
    {
    }

    /**
     * @return JsonResponse
     */
    public function cbrDailyRates(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $xmlResponseData = $this->requestService->sendXmlRequest(config('services.cbr.url'));

            $date = $xmlResponseData['@attributes']['Date'];
            $date = Carbon::parse($date)->format('Y-m-d');

            if ($existingData = $this->currencyRepository->findByDate($date)) {
                $currencyRequest = $existingData;
            } else {
                $title = $xmlResponseData['@attributes']['name'];

                $data = [
                    'title' => $title,
                    'date' => $date
                ];

                $rates = [];
                foreach ($xmlResponseData['Valute'] as $valute) {
                    $rates[] = [
                        'title' => $valute['Name'],
                        'num_code' => $valute['NumCode'],
                        'char_code' => $valute['CharCode'],
                        'units' => $valute['Nominal'],
                        'rate' => (float)str_replace(',', '.', $valute['Value']),
                        'inverse_rate' => (float)str_replace(',', '.', $valute['VunitRate']),
                    ];
                }
                $data['rates'] = $rates;
                $currencyRequest = $this->currencyRepository->create($data);
            }

            DB::commit();

            return Response::json(
                data: new CurrencyResource($currencyRequest),
                options: JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(
                data: [
                    'error' => 'Failed to retrieve currency rates.'
                ],
                status: 500
            );
        }
    }


}
