<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleReviewService
{
    protected $apiKey;
    protected $placeId;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_API_KEY');
        $this->placeId = env('GOOGLE_PLACE_ID'); // Your Google Place ID
    }

    public function fetchReviews()
    {
        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$this->placeId}&fields=reviews&key={$this->apiKey}";

        $response = Http::get($url);
        $data = $response->json();

        return $data['result']['reviews'] ?? [];
    }
}
