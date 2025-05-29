<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522131439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarification DROP purcharse_price, DROP fixed_fee_public, DROP recharge_time_public, DROP parking_time_public, DROP fixed_fee_resale');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarification ADD purcharse_price VARCHAR(255) NOT NULL, ADD fixed_fee_public VARCHAR(255) DEFAULT NULL, ADD recharge_time_public VARCHAR(255) DEFAULT NULL, ADD parking_time_public VARCHAR(255) DEFAULT NULL, ADD fixed_fee_resale VARCHAR(255) DEFAULT NULL');
    }
}