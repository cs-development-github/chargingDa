<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321110607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE charging_station_documentation (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) NOT NULL, ocpp VARCHAR(255) DEFAULT NULL, napn VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE charging_station_documentation_charging_stations (charging_station_documentation_id INT NOT NULL, charging_stations_id INT NOT NULL, INDEX IDX_43CD6F6A4C45B17 (charging_station_documentation_id), INDEX IDX_43CD6F6A5FD1012 (charging_stations_id), PRIMARY KEY(charging_station_documentation_id, charging_stations_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE charging_station_documentation_charging_stations ADD CONSTRAINT FK_43CD6F6A4C45B17 FOREIGN KEY (charging_station_documentation_id) REFERENCES charging_station_documentation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE charging_station_documentation_charging_stations ADD CONSTRAINT FK_43CD6F6A5FD1012 FOREIGN KEY (charging_stations_id) REFERENCES charging_stations (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE borne_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE borne_id (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE charging_station_documentation_charging_stations DROP FOREIGN KEY FK_43CD6F6A4C45B17');
        $this->addSql('ALTER TABLE charging_station_documentation_charging_stations DROP FOREIGN KEY FK_43CD6F6A5FD1012');
        $this->addSql('DROP TABLE charging_station_documentation');
        $this->addSql('DROP TABLE charging_station_documentation_charging_stations');
    }
}
