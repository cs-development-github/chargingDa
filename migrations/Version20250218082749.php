<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218082749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention CHANGE sim_id sim_id INT NOT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABF81AF80C FOREIGN KEY (sim_id) REFERENCES sim_card (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D11814ABF81AF80C ON intervention (sim_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABF81AF80C');
        $this->addSql('DROP INDEX UNIQ_D11814ABF81AF80C ON intervention');
        $this->addSql('ALTER TABLE intervention CHANGE sim_id sim_id VARCHAR(255) NOT NULL');
    }
}
