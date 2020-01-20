<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class Googlebook{

    public function searchBooks(string $word) :array
    {

      $data = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($word) . "&maxResults=5";

      $client = new Client();

      $response = $client->get($data,['http_errors' => false]);

      return json_decode($response->getBody()->getContents(),true);

    }

}
