<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $names = [
            'Electronics' => [
                'Smartphone',
                'Laptop',
                'Tablet',
                'Headphones',
                'Camera',
                'Smartwatch',
                'Television',
                'Speaker',
                'Printer',
                'Router'
            ],
            'Books' => [
                'Novel',
                'Textbook',
                'Comics',
                'Dictionary',
                'Biography',
                'Poetry',
                'Science Fiction',
                'Fantasy',
                'History'
            ]
        ];

        foreach ($names as $group => $items) {
            foreach ($items as $item) {
                $category = new Category();
                $category->setName($item);
                $manager->persist($category);
            }
        }

        $manager->flush();
    }
}
