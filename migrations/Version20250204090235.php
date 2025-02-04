<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204090235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manufacturer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE charging_stations ADD manufacturer_id INT NOT NULL, DROP manufacturer, DROP manufacturer_image');
        $this->addSql('ALTER TABLE charging_stations ADD CONSTRAINT FK_CBDA0A93A23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('CREATE INDEX IDX_CBDA0A93A23B42D ON charging_stations (manufacturer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE charging_stations DROP FOREIGN KEY FK_CBDA0A93A23B42D');
        $this->addSql('DROP TABLE manufacturer');
        $this->addSql('DROP INDEX IDX_CBDA0A93A23B42D ON charging_stations');
        $this->addSql('ALTER TABLE charging_stations ADD manufacturer VARCHAR(255) NOT NULL, ADD manufacturer_image VARCHAR(255) DEFAULT NULL, DROP manufacturer_id');
    }
}
