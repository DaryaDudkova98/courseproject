<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SalesforceService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private HttpClientInterface $httpClient;
    
    private string $authUrl;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $authUrl = 'https://login.salesforce.com',
        ?HttpClientInterface $httpClient = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->authUrl = $authUrl;
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function getClientCredentialsToken(): array
    {
        $response = $this->httpClient->request('POST', 'https://orgfarm-23621e67d8-dev-ed.develop.my.salesforce.com/services/oauth2/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]),
        ]);
        
        return $response->toArray();
    }

    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    public function getAuthUrl(string $state, ?string &$codeVerifier = null): string
    {
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'api refresh_token offline_access',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ];
        
        return 'https://orgfarm-23621e67d8-dev-ed.develop.my.salesforce.com/services/oauth2/authorize?' . http_build_query($params);
    }

    public function getAuthUrlWithoutPkce(string $state): string
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'api refresh_token offline_access',
            'state' => $state
        ];
        
        return 'https://orgfarm-23621e67d8-dev-ed.develop.my.salesforce.com/services/oauth2/authorize?' . http_build_query($params);
    }
    
    public function getAccessTokenWithPkce(string $code, string $codeVerifier): array
    {
        $response = $this->httpClient->request('POST', 'https://orgfarm-23621e67d8-dev-ed.develop.my.salesforce.com/services/oauth2/token', [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'code_verifier' => $codeVerifier
            ]
        ]);
        
        return $response->toArray();
    }

    public function getAccessToken(string $code): array
    {
        $response = $this->httpClient->request('POST', 'https://orgfarm-23621e67d8-dev-ed.develop.my.salesforce.com/services/oauth2/token', [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri
            ]
        ]);
        
        return $response->toArray();
    }
    
    public function getAccessTokenUniversal(string $code, ?string $codeVerifier = null): array
    {
        if ($codeVerifier !== null) {
            return $this->getAccessTokenWithPkce($code, $codeVerifier);
        } else {
            return $this->getAccessToken($code);
        }
    }

    public function createAccount(string $accessToken, string $instanceUrl, array $data): array
    {
        $response = $this->httpClient->request('POST', $instanceUrl . '/services/data/v59.0/sobjects/Account', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ],
            'json' => $data
        ]);
        
        return $response->toArray();
    }
    
    public function createContact(string $accessToken, string $instanceUrl, array $data): array
    {
        $response = $this->httpClient->request('POST', $instanceUrl . '/services/data/v59.0/sobjects/Contact', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ],
            'json' => $data
        ]);
        
        return $response->toArray();
    }
    
    public function refreshToken(string $refreshToken): array
    {
        $response = $this->httpClient->request('POST', 'https://orgfarm-23621e67d8-dev-ed.develop.my.salesforce.comservices/oauth2/token', [
            'body' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]
        ]);
        
        return $response->toArray();
    }
    
    public function query(string $accessToken, string $instanceUrl, string $soql): array
    {
        $response = $this->httpClient->request('GET', $instanceUrl . '/services/data/v59.0/query', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ],
            'query' => ['q' => $soql]
        ]);
        
        return $response->toArray();
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }
    
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }
}