<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221143008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sim_card DROP FOREIGN KEY FK_60AA437D296CD8AE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649296CD8AE');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FB03A8386');
        $this->addSql('DROP TABLE team');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB5A384417');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABF81AF80C');
        $this->addSql('DROP INDEX UNIQ_D11814ABF81AF80C ON intervention');
        $this->addSql('DROP INDEX IDX_D11814AB5A384417 ON intervention');
        $this->addSql('ALTER TABLE intervention ADD installator_id INT DEFAULT NULL, ADD sim VARCHAR(255) NOT NULL, DROP sim_id, CHANGE installer_id client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABE71A9910 FOREIGN KEY (installator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D11814AB19EB6921 ON intervention (client_id)');
        $this->addSql('CREATE INDEX IDX_D11814ABE71A9910 ON intervention (installator_id)');
        $this->addSql('DROP INDEX IDX_60AA437D296CD8AE ON sim_card');
        $this->addSql('ALTER TABLE sim_card DROP team_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B03A8386');
        $this->addSql('DROP INDEX IDX_8D93D649B03A8386 ON user');
        $this->addSql('DROP INDEX IDX_8D93D649296CD8AE ON user');
        $this->addSql('ALTER TABLE user DROP created_by_id, DROP team_id, DROP siret, DROP is_chef_effectif, CHANGE society_name society_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C4E0A61FB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB19EB6921');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABE71A9910');
        $this->addSql('DROP INDEX IDX_D11814AB19EB6921 ON intervention');
        $this->addSql('DROP INDEX IDX_D11814ABE71A9910 ON intervention');
        $this->addSql('ALTER TABLE intervention ADD installer_id INT DEFAULT NULL, ADD sim_id INT NOT NULL, DROP client_id, DROP installator_id, DROP sim');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB5A384417 FOREIGN KEY (installer_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABF81AF80C FOREIGN KEY (sim_id) REFERENCES sim_card (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D11814ABF81AF80C ON intervention (sim_id)');
        $this->addSql('CREATE INDEX IDX_D11814AB5A384417 ON intervention (installer_id)');
        $this->addSql('ALTER TABLE sim_card ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sim_card ADD CONSTRAINT FK_60AA437D296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_60AA437D296CD8AE ON sim_card (team_id)');
        $this->addSql('ALTER TABLE user ADD created_by_id INT DEFAULT NULL, ADD team_id INT DEFAULT NULL, ADD siret VARCHAR(14) DEFAULT NULL, ADD is_chef_effectif TINYINT(1) DEFAULT 0 NOT NULL, CHANGE society_name society_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D649B03A8386 ON user (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649296CD8AE ON user (team_id)');
    }
}
