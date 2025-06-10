<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610100438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, full_address VARCHAR(255) NOT NULL, street_number VARCHAR(255) DEFAULT NULL, street_name VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, latitude VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, number VARCHAR(255) NOT NULL, INDEX IDX_FEF0481D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE charging_station_documentation (id INT AUTO_INCREMENT NOT NULL, charging_station_id INT DEFAULT NULL, image VARCHAR(255) NOT NULL, ocpp VARCHAR(255) DEFAULT NULL, napn VARCHAR(255) DEFAULT NULL, INDEX IDX_BA7B02C534D723C9 (charging_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE charging_station_setting (id INT AUTO_INCREMENT NOT NULL, charging_station_id INT DEFAULT NULL, client_id INT DEFAULT NULL, public TINYINT(1) DEFAULT NULL, address_line VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, latitude VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, INDEX IDX_2C1F8EB934D723C9 (charging_station_id), INDEX IDX_2C1F8EB919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE charging_stations (id INT AUTO_INCREMENT NOT NULL, manufacturer_id INT NOT NULL, model VARCHAR(255) NOT NULL, connectors INT NOT NULL, image VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, slug VARCHAR(255) NOT NULL, power INT DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_CBDA0A93989D9B62 (slug), INDEX IDX_CBDA0A93A23B42D (manufacturer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, society_name VARCHAR(255) NOT NULL, siret VARCHAR(255) DEFAULT NULL, number_tva VARCHAR(255) DEFAULT NULL, code_naf VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, secure_token VARCHAR(255) DEFAULT NULL, legal_form VARCHAR(255) DEFAULT NULL, signature_transaction_id VARCHAR(255) DEFAULT NULL, document_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C7440455DA508F53 (secure_token), UNIQUE INDEX UNIQ_C7440455F5B7AF75 (address_id), INDEX IDX_C7440455B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, installator_id INT DEFAULT NULL, charging_station_id INT DEFAULT NULL, sim VARCHAR(255) NOT NULL, borne_name VARCHAR(255) DEFAULT NULL, reference VARCHAR(64) DEFAULT NULL, INDEX IDX_D11814AB19EB6921 (client_id), INDEX IDX_D11814ABE71A9910 (installator_id), INDEX IDX_D11814AB34D723C9 (charging_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manufacturer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sim_card (id INT AUTO_INCREMENT NOT NULL, activate_code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarification (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, charging_station_id INT DEFAULT NULL, resale_price VARCHAR(255) DEFAULT NULL, reduced_price VARCHAR(255) DEFAULT NULL, public_price VARCHAR(255) DEFAULT NULL, recharge_time_resale VARCHAR(255) DEFAULT NULL, parking_time_resale VARCHAR(255) DEFAULT NULL, offer_type VARCHAR(255) DEFAULT NULL, INDEX IDX_613281619EB6921 (client_id), INDEX IDX_613281634D723C9 (charging_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, phone VARCHAR(255) DEFAULT NULL, society_name VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, siret VARCHAR(255) DEFAULT NULL, email_verified_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge ADD CONSTRAINT FK_FEF0481D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE charging_station_documentation ADD CONSTRAINT FK_BA7B02C534D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
        $this->addSql('ALTER TABLE charging_station_setting ADD CONSTRAINT FK_2C1F8EB934D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
        $this->addSql('ALTER TABLE charging_station_setting ADD CONSTRAINT FK_2C1F8EB919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE charging_stations ADD CONSTRAINT FK_CBDA0A93A23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABE71A9910 FOREIGN KEY (installator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB34D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tarification ADD CONSTRAINT FK_613281619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE tarification ADD CONSTRAINT FK_613281634D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE badge DROP FOREIGN KEY FK_FEF0481D19EB6921');
        $this->addSql('ALTER TABLE charging_station_documentation DROP FOREIGN KEY FK_BA7B02C534D723C9');
        $this->addSql('ALTER TABLE charging_station_setting DROP FOREIGN KEY FK_2C1F8EB934D723C9');
        $this->addSql('ALTER TABLE charging_station_setting DROP FOREIGN KEY FK_2C1F8EB919EB6921');
        $this->addSql('ALTER TABLE charging_stations DROP FOREIGN KEY FK_CBDA0A93A23B42D');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F5B7AF75');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455B03A8386');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB19EB6921');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABE71A9910');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB34D723C9');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE tarification DROP FOREIGN KEY FK_613281619EB6921');
        $this->addSql('ALTER TABLE tarification DROP FOREIGN KEY FK_613281634D723C9');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE charging_station_documentation');
        $this->addSql('DROP TABLE charging_station_setting');
        $this->addSql('DROP TABLE charging_stations');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE manufacturer');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE sim_card');
        $this->addSql('DROP TABLE tarification');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
