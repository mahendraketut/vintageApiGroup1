<?php

namespace App\Traits;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

trait checkShippingCostTraits
{
    //get API Key from .env RAJA_0NGKIR_KEY
    private function getApiKey()
    {
        return env('RAJA_ONGKIR_KEY');
    }

    /**
     * Method for calculate shipping cost
     * Weight in grams
     * Specify courier (e.g., jne, pos, tiki)
     *
     * @param int $origin
     * @param int $destination
     * @param number $weight
     * @param string $courier
     */
    public function calculateCost($origin, $destination, $weight, $courier)
    {

        $apiKey = $this->getApiKey();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('RAJA_ONGKIR_BASE_URL') . "/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=" . $origin . "&destination=" . $destination . "&weight=" . $weight . "&courier=" . $courier,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: " . $apiKey
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }

    /**
     * Extract shipping cost details from RajaOngkir API response.
     *
     * @param array|null $shippingCostData
     * @return array|null
     */
    public function extractShippingCostDetails($shippingCostData)
    {
        if (!$shippingCostData || !isset($shippingCostData['rajaongkir']['results'][0]['costs'])) {
            return null; // Return null if shipping cost data is invalid or not present
        }

        $result = $shippingCostData['rajaongkir']['results'][0]['costs'][0];
        $service = $result['service'];
        $description = $result['description'];
        $etd = $result['cost'][0]['etd'];
        $cost = $result['cost'][0]['value'];

        return [
            'service' => "{$service} - {$description}",
            'etd' => $etd,
            'cost' => $cost,
        ];
    }

    /**
     * Method for get the city ID from RajaOngkir API
     *
     * @param string $cityName
     */
    public function getCityId(string $cityName)
    {
        $url = env('RAJA_ONGKIR_BASE_URL') . '/city'; // Get the RajaOngkir API URL from .env file (RAJA_ONGKIR_URL
        $apiKey = $this->getApiKey();
        $client = new Client();

        try {
            // Make a request to RajaOngkir API to get the city list
            $response = $client->request('GET', $url, [
                'headers' => [
                    'key' => $apiKey,
                ],
            ]);

            $response = json_decode($response->getBody(), true);

            // Log the response for debugging
            Log::info('RajaOngkir city list response:', $response);

            if (isset($response['rajaongkir']['results'])) {
                foreach ($response['rajaongkir']['results'] as $city) {
                    // Normalize city names for case-insensitive comparison
                    if (strtolower($city['city_name']) == strtolower($cityName)) {
                        return $city['city_id'];
                    }
                }
            } else {
                Log::warning('RajaOngkir city list response does not contain "results" key');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching city list from RajaOngkir API: ' . $e->getMessage());
        }

        return 0; // Return 0 if city ID is not found or error occurs
    }
}
