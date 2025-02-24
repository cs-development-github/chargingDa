<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224094007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE charging_station_setting (id INT AUTO_INCREMENT NOT NULL, charging_station_id INT DEFAULT NULL, client_id INT DEFAULT NULL, public TINYINT(1) DEFAULT NULL, adress LONGTEXT NOT NULL, installed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', supervised_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2C1F8EB934D723C9 (charging_station_id), INDEX IDX_2C1F8EB919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE charging_station_setting ADD CONSTRAINT FK_2C1F8EB934D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
        $this->addSql('ALTER TABLE charging_station_setting ADD CONSTRAINT FK_2C1F8EB919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE charging_station_setting DROP FOREIGN KEY FK_2C1F8EB934D723C9');
        $this->addSql('ALTER TABLE charging_station_setting DROP FOREIGN KEY FK_2C1F8EB919EB6921');
        $this->addSql('DROP TABLE charging_station_setting');
    }
}
