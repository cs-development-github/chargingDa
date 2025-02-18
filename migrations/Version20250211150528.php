<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211150528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE lastname lastname VARCHAR(255) DEFAULT NULL, CHANGE siret siret VARCHAR(255) DEFAULT NULL, CHANGE number_tva number_tva VARCHAR(255) DEFAULT NULL, CHANGE code_naf code_naf VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE price_kwh price_kwh VARCHAR(255) DEFAULT NULL, CHANGE price_resale price_resale VARCHAR(255) DEFAULT NULL, CHANGE adress adress LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE name name VARCHAR(255) NOT NULL, CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE siret siret VARCHAR(255) NOT NULL, CHANGE number_tva number_tva VARCHAR(255) NOT NULL, CHANGE code_naf code_naf VARCHAR(255) NOT NULL, CHANGE phone phone VARCHAR(255) NOT NULL, CHANGE price_kwh price_kwh VARCHAR(255) NOT NULL, CHANGE price_resale price_resale VARCHAR(255) NOT NULL, CHANGE adress adress LONGTEXT NOT NULL');
    }
}
