<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class Inventory/InventoryVisibilityControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inventory/inventory/visibility');

        self::assertResponseIsSuccessful();
    }
}
