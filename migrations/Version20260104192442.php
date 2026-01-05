<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104192442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_item_user (card_item_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (card_item_id, user_id))');
        $this->addSql('CREATE INDEX IDX_31AB71E64575D828 ON card_item_user (card_item_id)');
        $this->addSql('CREATE INDEX IDX_31AB71E6A76ED395 ON card_item_user (user_id)');
        $this->addSql('CREATE TABLE inventory_user (inventory_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (inventory_id, user_id))');
        $this->addSql('CREATE INDEX IDX_C73519679EEA759 ON inventory_user (inventory_id)');
        $this->addSql('CREATE INDEX IDX_C7351967A76ED395 ON inventory_user (user_id)');
        $this->addSql('CREATE TABLE item_user (item_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (item_id, user_id))');
        $this->addSql('CREATE INDEX IDX_45A392B2126F525E ON item_user (item_id)');
        $this->addSql('CREATE INDEX IDX_45A392B2A76ED395 ON item_user (user_id)');
        $this->addSql('ALTER TABLE card_item_user ADD CONSTRAINT FK_31AB71E64575D828 FOREIGN KEY (card_item_id) REFERENCES card_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_item_user ADD CONSTRAINT FK_31AB71E6A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inventory_user ADD CONSTRAINT FK_C73519679EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inventory_user ADD CONSTRAINT FK_C7351967A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_user ADD CONSTRAINT FK_45A392B2126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_user ADD CONSTRAINT FK_45A392B2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_item ADD public BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE card_item ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE card_item ADD CONSTRAINT FK_F32827BC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id)');
        $this->addSql('CREATE INDEX IDX_F32827BC7E3C61F9 ON card_item (owner_id)');
        $this->addSql('ALTER TABLE inventory ADD public BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE inventory ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE inventory DROP name');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A367E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_B12D4A367E3C61F9 ON inventory (owner_id)');
        $this->addSql('ALTER TABLE item ADD public BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE item ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E7E3C61F9 ON item (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_item_user DROP CONSTRAINT FK_31AB71E64575D828');
        $this->addSql('ALTER TABLE card_item_user DROP CONSTRAINT FK_31AB71E6A76ED395');
        $this->addSql('ALTER TABLE inventory_user DROP CONSTRAINT FK_C73519679EEA759');
        $this->addSql('ALTER TABLE inventory_user DROP CONSTRAINT FK_C7351967A76ED395');
        $this->addSql('ALTER TABLE item_user DROP CONSTRAINT FK_45A392B2126F525E');
        $this->addSql('ALTER TABLE item_user DROP CONSTRAINT FK_45A392B2A76ED395');
        $this->addSql('DROP TABLE card_item_user');
        $this->addSql('DROP TABLE inventory_user');
        $this->addSql('DROP TABLE item_user');
        $this->addSql('ALTER TABLE card_item DROP CONSTRAINT FK_F32827BC7E3C61F9');
        $this->addSql('DROP INDEX IDX_F32827BC7E3C61F9');
        $this->addSql('ALTER TABLE card_item DROP public');
        $this->addSql('ALTER TABLE card_item DROP owner_id');
        $this->addSql('ALTER TABLE inventory DROP CONSTRAINT FK_B12D4A367E3C61F9');
        $this->addSql('DROP INDEX IDX_B12D4A367E3C61F9');
        $this->addSql('ALTER TABLE inventory ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE inventory DROP public');
        $this->addSql('ALTER TABLE inventory DROP owner_id');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E7E3C61F9');
        $this->addSql('DROP INDEX IDX_1F1B251E7E3C61F9');
        $this->addSql('ALTER TABLE item DROP public');
        $this->addSql('ALTER TABLE item DROP owner_id');
    }
}
