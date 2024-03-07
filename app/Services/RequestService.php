<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RequestService
{
    /**
     * @param string $url
     * @param int $maxAttempts
     * @param int $retryDelayMilliseconds
     * @return array
     * @throws \Exception
     */
    public function sendXmlRequest(string $url, int $maxAttempts = 3, int $retryDelayMilliseconds = 1000): array
    {
        return retry(
            $maxAttempts,
            function () use ($url) {
                return $this->makeXmlRequest($url);
            },
            $retryDelayMilliseconds
        );
    }

    /**
     * @param string $url
     * @return array
     * @throws \Exception
     */
    private function makeXmlRequest(string $url): array
    {
        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $xmlData = simplexml_load_string($response->body());
                $json = json_encode($xmlData);

                return json_decode($json, true);
            } else {
                throw new \Exception('HTTP request was not successful. Status code: ' . $response->status());
            }
        } catch (\Exception $exception) {
            Log::error('Error in sendXmlRequest: ' . $exception->getMessage());
            throw $exception;
        }
    }
}
