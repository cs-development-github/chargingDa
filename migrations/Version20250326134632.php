<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326134632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarification ADD fixed_fee_public VARCHAR(255) DEFAULT NULL, ADD recharge_time_public VARCHAR(255) DEFAULT NULL, ADD parking_time_public VARCHAR(255) DEFAULT NULL, ADD fixed_fee_resale VARCHAR(255) DEFAULT NULL, ADD recharge_time_resale VARCHAR(255) DEFAULT NULL, ADD parking_time_resale VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarification DROP fixed_fee_public, DROP recharge_time_public, DROP parking_time_public, DROP fixed_fee_resale, DROP recharge_time_resale, DROP parking_time_resale');
    }
}
