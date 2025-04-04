<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403115819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, full_address VARCHAR(255) NOT NULL, street_number VARCHAR(255) DEFAULT NULL, street_name VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, latitude VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE charging_station_documentation (id INT AUTO_INCREMENT NOT NULL, charging_station_id INT DEFAULT NULL, image VARCHAR(255) NOT NULL, ocpp VARCHAR(255) DEFAULT NULL, napn VARCHAR(255) DEFAULT NULL, INDEX IDX_BA7B02C534D723C9 (charging_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE charging_station_documentation ADD CONSTRAINT FK_BA7B02C534D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_stations (id)');
        $this->addSql('ALTER TABLE charging_station_setting ADD address_line VARCHAR(255) DEFAULT NULL, ADD postal_code VARCHAR(10) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD country VARCHAR(255) DEFAULT NULL, ADD latitude VARCHAR(255) DEFAULT NULL, ADD longitude VARCHAR(255) DEFAULT NULL, ADD region VARCHAR(255) DEFAULT NULL, ADD department VARCHAR(255) DEFAULT NULL, DROP adress, DROP installed_at, DROP supervised_at');
        $this->addSql('ALTER TABLE charging_stations DROP documentation');
        $this->addSql('ALTER TABLE client ADD address_id INT DEFAULT NULL, ADD signature_transaction_id VARCHAR(255) DEFAULT NULL, ADD document_id VARCHAR(255) DEFAULT NULL, DROP adress, DROP otp_code, DROP otp_expires_at, DROP is_otp_verified');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455F5B7AF75 ON client (address_id)');
        $this->addSql('ALTER TABLE intervention ADD borne_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tarification ADD fixed_fee_public VARCHAR(255) DEFAULT NULL, ADD recharge_time_public VARCHAR(255) DEFAULT NULL, ADD parking_time_public VARCHAR(255) DEFAULT NULL, ADD fixed_fee_resale VARCHAR(255) DEFAULT NULL, ADD recharge_time_resale VARCHAR(255) DEFAULT NULL, ADD parking_time_resale VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL, ADD siret VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F5B7AF75');
        $this->addSql('ALTER TABLE charging_station_documentation DROP FOREIGN KEY FK_BA7B02C534D723C9');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE charging_station_documentation');
        $this->addSql('ALTER TABLE intervention DROP borne_name');
        $this->addSql('DROP INDEX UNIQ_C7440455F5B7AF75 ON client');
        $this->addSql('ALTER TABLE client ADD adress LONGTEXT DEFAULT NULL, ADD otp_code VARCHAR(6) DEFAULT NULL, ADD otp_expires_at DATETIME DEFAULT NULL, ADD is_otp_verified TINYINT(1) NOT NULL, DROP address_id, DROP signature_transaction_id, DROP document_id');
        $this->addSql('ALTER TABLE charging_stations ADD documentation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tarification DROP fixed_fee_public, DROP recharge_time_public, DROP parking_time_public, DROP fixed_fee_resale, DROP recharge_time_resale, DROP parking_time_resale');
        $this->addSql('ALTER TABLE user DROP is_verified, DROP siret');
        $this->addSql('ALTER TABLE charging_station_setting ADD adress LONGTEXT DEFAULT NULL, ADD installed_at DATETIME DEFAULT NULL, ADD supervised_at DATETIME DEFAULT NULL, DROP address_line, DROP postal_code, DROP city, DROP country, DROP latitude, DROP longitude, DROP region, DROP department');
    }
}
