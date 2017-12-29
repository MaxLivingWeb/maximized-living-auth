<?php

namespace App\Helpers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class ShopifyHelper
{
    private $client;

    function __construct()
    {
        $this->client = new GuzzleClient([
            'base_uri' => 'https://' . env('SHOPIFY_API_KEY') . ':' . env('SHOPIFY_API_PASSWORD') . '@' . env('SHOPIFY_API_STORE') . '.myshopify.com/admin/'
        ]);
    }

    public function getOrCreateCustomer($customer)
    {
        //search for existing customer
        $result = $this->client->get('customers/search.json?query=email:' . $customer['email']);

        $customers = json_decode($result->getBody()->getContents())->customers;
        if(count($customers) > 0) {
            return $customers[0];
        }

        //no matching customer found, create new customer
        $result = $this->client->post('customers.json', [
            'json' => [
                'customer' => $customer
            ]
        ]);

        return json_decode($result->getBody()->getContents())->customer;
    }
}
