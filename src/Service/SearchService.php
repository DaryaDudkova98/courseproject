<?php

namespace App\Service;

use Elastic\Elasticsearch\Client;

class SearchService
{
    public function __construct(private Client $client)
    {
    }

    public function search(string $query): array
    {
        if (!$query) {
            return [];
        }

        $response = $this->client->search([
            'index' => 'app',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query'  => $query,
                        'fields' => ['title', 'content']
                    ]
                ]
            ]
        ]);

        return $response['hits']['hits'] ?? [];
    }
}
