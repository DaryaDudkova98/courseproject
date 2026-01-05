<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class Inventory/InventoryWritersControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inventory/inventory/writers');

        self::assertResponseIsSuccessful();
    }
}
