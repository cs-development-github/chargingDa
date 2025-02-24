<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221141811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, number VARCHAR(255) NOT NULL, INDEX IDX_FEF0481D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarification (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, charging_station_id INT DEFAULT NULL, purcharse_price VARCHAR(255) NOT NULL, resale_price VARCHAR(255) NOT NULL, reduced_price VARCHAR(255) NOT NULL, INDEX IDX_613281619EB6921 (client_id), INDEX IDX_613281634D723C9 (charging_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge ADD CONSTRAINT FK_FEF0481D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE tarification ADD CONSTRAINT FK_613281619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE tarification ADD CONSTRAINT FK_613281634D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE badge DROP FOREIGN KEY FK_FEF0481D19EB6921');
        $this->addSql('ALTER TABLE tarification DROP FOREIGN KEY FK_613281619EB6921');
        $this->addSql('ALTER TABLE tarification DROP FOREIGN KEY FK_613281634D723C9');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE tarification');
    }
}
