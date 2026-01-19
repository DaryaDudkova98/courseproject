<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SalesforceController extends AbstractController
{
    #[Route('/salesforce/export-form', name: 'salesforce_export_form', methods: ['GET'])]
    public function showExportForm(): Response
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/salesforce.html.twig', [
            'app' => ['user' => ['id' => $user->getId()]]
        ]);
    }

    #[Route('/salesforce/create-contact', name: 'salesforce_create_contact', methods: ['POST'])]
    public function createContact(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $client = HttpClient::create();
            
            $response = $client->request('POST',
                'https://orgfarm-23621e67d8-dev-ed.develop.my.salesforce.com/services/oauth2/token', [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'body' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $_ENV['SALESFORCE_CLIENT_ID'],
                        'client_secret' => $_ENV['SALESFORCE_CLIENT_SECRET'],
                    ],
                ]);
            
            $tokenData = $response->toArray();
            $accessToken = $tokenData['access_token'];
            $instanceUrl = $tokenData['instance_url'];

            $firstName = trim($request->request->get('first_name', ''));
            $lastName = trim($request->request->get('last_name', ''));
            $position = trim($request->request->get('position', ''));
            $phone = trim($request->request->get('phone', ''));
            $formUserId = $request->request->get('user_id');

            $userId = $user->getId();
            $userEmail = $user->getEmail();

            if (empty($firstName) || empty($lastName)) {
                throw new \RuntimeException('First name and last name are required');
            }

            if ($formUserId != $userId) {
                throw new \RuntimeException('User mismatch');
            }

            $existingContact = $this->checkExistingContact($userEmail, $accessToken, $instanceUrl);
            
            if ($existingContact) {
                return $this->handleExistingContact(
                    $existingContact,
                    $firstName,
                    $lastName,
                    $position,
                    $phone,
                    $userEmail,
                    $accessToken,
                    $instanceUrl,
                    $user
                );
            }

            return $this->createNewContactAndAccount(
                $firstName,
                $lastName,
                $position,
                $phone,
                $userEmail,
                $accessToken,
                $instanceUrl,
                $user
            );

        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Error: ' . $e->getMessage());
            return $this->redirectToRoute('admin_profile_index', ['entityId' => $user->getId()]);
        }
    }

    private function handleExistingContact(
        array $existingContact,
        string $firstName,
        string $lastName,
        string $position,
        string $phone,
        string $email,
        string $accessToken,
        string $instanceUrl,
        User $user
    ): Response {
        $client = HttpClient::create();
        
        $hasChanges = false;
        $changes = [];
        
        if ($existingContact['FirstName'] !== $firstName) {
            $hasChanges = true;
            $changes[] = "First Name: '{$existingContact['FirstName']}' → '{$firstName}'";
        }
        
        if ($existingContact['LastName'] !== $lastName) {
            $hasChanges = true;
            $changes[] = "Last Name: '{$existingContact['LastName']}' → '{$lastName}'";
        }

        $existingPosition = $existingContact['Title'] ?? '';
        if ($existingPosition !== $position) {
            $hasChanges = true;
            $changes[] = "Position: '" . ($existingPosition ?: 'empty') . "' → '" . ($position ?: 'empty') . "'";
        }

        $existingPhone = $existingContact['Phone'] ?? '';
        if ($existingPhone !== $phone) {
            $hasChanges = true;
            $changes[] = "Phone: '" . ($existingPhone ?: 'empty') . "' → '" . ($phone ?: 'empty') . "'";
        }

        $existingAccount = $this->getAccountDetails($existingContact['AccountId'], $accessToken, $instanceUrl);
        if ($existingAccount) {
            $companyName = $firstName . ' ' . $lastName . ' (Individual)';
            
            if ($existingAccount['Name'] !== $companyName) {
                $hasChanges = true;
                $changes[] = "Account Name: '{$existingAccount['Name']}' → '{$companyName}'";
            }
            
            if ($existingAccount['Phone'] !== $phone) {
                $hasChanges = true;
                $changes[] = "Account Phone: '" . ($existingAccount['Phone'] ?? 'empty') . "' → '" . ($phone ?: 'empty') . "'";
            }
        }
        
        if (!$hasChanges) {

            $this->addFlash('info',
                'ℹ️ Contact "' . $existingContact['FirstName'] . ' ' . $existingContact['LastName'] . 
                '" already exists in Salesforce with identical data. No updates were made.'
            );
            return $this->redirectToRoute('admin_profile_index', ['entityId' => $user->getId()]);
        }
        
        try {
            $contactUpdateData = [];
            
            if ($existingContact['FirstName'] !== $firstName) {
                $contactUpdateData['FirstName'] = $firstName;
            }
            
            if ($existingContact['LastName'] !== $lastName) {
                $contactUpdateData['LastName'] = $lastName;
            }
            
            if ($existingPosition !== $position) {
                $contactUpdateData['Title'] = $position;
            }
            
            if ($existingPhone !== $phone) {
                $contactUpdateData['Phone'] = $phone;
            }
            
            if (!empty($contactUpdateData)) {
                $contactResponse = $client->request('PATCH',
                    $instanceUrl . '/services/data/v59.0/sobjects/Contact/' . $existingContact['Id'], [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json'
                        ],
                        'json' => $contactUpdateData
                    ]);
                
                if ($contactResponse->getStatusCode() !== 204) {
                    throw new \RuntimeException('Failed to update contact');
                }
            }
            
            if ($existingAccount) {
                $accountUpdateData = [];
                $companyName = $firstName . ' ' . $lastName . ' (Individual)';
                
                if ($existingAccount['Name'] !== $companyName) {
                    $accountUpdateData['Name'] = $companyName;
                }
                
                $accountPhone = $existingAccount['Phone'] ?? '';
                if ($accountPhone !== $phone) {
                    $accountUpdateData['Phone'] = $phone;
                }
                
                if (!empty($accountUpdateData)) {
                    $accountResponse = $client->request('PATCH',
                        $instanceUrl . '/services/data/v59.0/sobjects/Account/' . $existingContact['AccountId'], [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $accessToken,
                                'Content-Type' => 'application/json'
                            ],
                            'json' => $accountUpdateData
                        ]);
                    
                    if ($accountResponse->getStatusCode() !== 204) {
                        throw new \RuntimeException('Failed to update account');
                    }
                }
            }
            
            $changeMessage = "Updated fields:\n" . implode("\n", $changes);
            $this->addFlash('success',
                '✅ Contact "' . $firstName . ' ' . $lastName .
                '" updated in Salesforce!' . "\n\n" . $changeMessage
            );
            
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to update Salesforce records: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('admin_profile_index', ['entityId' => $user->getId()]);
    }

    private function createNewContactAndAccount(
        string $firstName,
        string $lastName,
        string $position,
        string $phone,
        string $email,
        string $accessToken,
        string $instanceUrl,
        User $user
    ): Response {
        $client = HttpClient::create();
        
        try {

            $companyName = $firstName . ' ' . $lastName . ' (Individual)';
            $accountData = [
                'Name' => $companyName,
                'Phone' => $phone,
                'Description' => 'Created from web form',
                'Type' => 'Customer',
                'Industry' => 'Technology'
            ];

            $accountResponse = $client->request('POST',
                $instanceUrl . '/services/data/v59.0/sobjects/Account', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json'
                    ],
                    'json' => $accountData
                ]);
            
            $accountResult = $accountResponse->toArray();

            if (!isset($accountResult['id']) || $accountResult['id'] === null) {
                throw new \RuntimeException('Failed to create account: ' . json_encode($accountResult));
            }

            $contactData = [
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'Email' => $email,
                'AccountId' => $accountResult['id']
            ];

            if (!empty($position)) {
                $contactData['Title'] = $position;
            }

            if (!empty($phone)) {
                $contactData['Phone'] = $phone;
            }

            $contactResponse = $client->request('POST',
                $instanceUrl . '/services/data/v59.0/sobjects/Contact', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json'
                    ],
                    'json' => $contactData
                ]);
            
            $contactResult = $contactResponse->toArray();

            if (!isset($contactResult['id']) || $contactResult['id'] === null) {
                throw new \RuntimeException('Failed to create contact: ' . json_encode($contactResult));
            }

            $this->addFlash('success',
                '✅ Account "' . $companyName . '" and Contact "' . $firstName . ' ' . $lastName .
                '" successfully created in Salesforce!'
            );

        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create Salesforce records: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('admin_profile_index', ['entityId' => $user->getId()]);
    }

    private function checkExistingContact(string $email, string $accessToken, string $instanceUrl): ?array
    {
        $client = HttpClient::create();
        
        $escapedEmail = str_replace("'", "\\'", $email);
        $soqlQuery = "SELECT Id, FirstName, LastName, Email, Title, Phone, AccountId FROM Contact WHERE Email = '{$escapedEmail}' LIMIT 1";
        $encodedQuery = urlencode($soqlQuery);
        
        try {
            $response = $client->request('GET',
                $instanceUrl . '/services/data/v59.0/query/?q=' . $encodedQuery, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json'
                    ]
                ]);
            
            $result = $response->toArray();
            
            if (isset($result['records']) && count($result['records']) > 0) {
                return $result['records'][0];
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log('Salesforce contact check failed: ' . $e->getMessage());
            return null;
        }
    }

    private function getAccountDetails(string $accountId, string $accessToken, string $instanceUrl): ?array
    {
        $client = HttpClient::create();
        
        try {
            $response = $client->request('GET',
                $instanceUrl . '/services/data/v59.0/sobjects/Account/' . $accountId, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json'
                    ]
                ]);
            
            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log('Failed to get account details: ' . $e->getMessage());
            return null;
        }
    }
}