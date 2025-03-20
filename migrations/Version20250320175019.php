<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320175019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD signature_transaction_id VARCHAR(255) DEFAULT NULL, DROP otp_code, DROP otp_expires_at, DROP is_otp_verified');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD otp_code VARCHAR(6) DEFAULT NULL, ADD otp_expires_at DATETIME DEFAULT NULL, ADD is_otp_verified TINYINT(1) NOT NULL, DROP signature_transaction_id');
    }
}
