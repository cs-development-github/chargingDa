<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326140848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE charging_station_setting ADD address_line VARCHAR(255) DEFAULT NULL, ADD postal_code VARCHAR(10) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD country VARCHAR(255) DEFAULT NULL, DROP adress, DROP installed_at, DROP supervised_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE charging_station_setting ADD adress LONGTEXT DEFAULT NULL, ADD installed_at DATETIME DEFAULT NULL, ADD supervised_at DATETIME DEFAULT NULL, DROP address_line, DROP postal_code, DROP city, DROP country');
    }
}
