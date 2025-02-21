<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220133552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, installator_id INT DEFAULT NULL, charging_station_id INT DEFAULT NULL, sim VARCHAR(255) NOT NULL, INDEX IDX_D11814AB19EB6921 (client_id), INDEX IDX_D11814ABE71A9910 (installator_id), INDEX IDX_D11814AB34D723C9 (charging_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABE71A9910 FOREIGN KEY (installator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB34D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB19EB6921');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABE71A9910');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB34D723C9');
        $this->addSql('DROP TABLE intervention');
    }
}
