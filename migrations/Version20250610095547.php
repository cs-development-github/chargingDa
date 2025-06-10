<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610095547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL, ADD email_verified_at DATETIME DEFAULT NULL, DROP created_by_id, DROP team_id, DROP is_chef_effectif, CHANGE siret siret VARCHAR(255) DEFAULT NULL, CHANGE society_name society_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD created_by_id INT DEFAULT NULL, ADD team_id INT DEFAULT NULL, ADD is_chef_effectif TINYINT(1) DEFAULT 0 NOT NULL, DROP is_verified, DROP email_verified_at, CHANGE society_name society_name VARCHAR(255) DEFAULT NULL, CHANGE siret siret VARCHAR(14) DEFAULT NULL');
    }
}
