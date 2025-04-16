<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416182940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE capteur (id_c INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, distance_mesuree DOUBLE PRECISION NOT NULL, date_mesure DATETIME DEFAULT CURRENT_TIMESTAMP, portee_maximale DOUBLE PRECISION NOT NULL, precision_capteur DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_5B4A1695F231B082 (poubelle_id), PRIMARY KEY(id_c)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE capteurp (id INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, quantite DOUBLE PRECISION NOT NULL, date_m DATETIME NOT NULL, UNIQUE INDEX UNIQ_239B36CF231B082 (poubelle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE centre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, longitude DOUBLE PRECISION NOT NULL, altitude DOUBLE PRECISION NOT NULL, telephone VARCHAR(8) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, employe_id INT NOT NULL, centre_id INT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, signature_path VARCHAR(700) NOT NULL, INDEX IDX_603499931B65292 (employe_id), INDEX IDX_60349993463CD7C3 (centre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historique (id INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, date_evenement DATETIME NOT NULL, type_evenement VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, quantite_dechets DOUBLE PRECISION NOT NULL, INDEX IDX_EDBFD5ECF231B082 (poubelle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poubelle (id INT AUTO_INCREMENT NOT NULL, centre_id INT DEFAULT NULL, adresse VARCHAR(255) NOT NULL, niveau INT NOT NULL, etat VARCHAR(255) NOT NULL, date_installation DATE NOT NULL, hauteur_totale INT NOT NULL, INDEX IDX_B5344EA3463CD7C3 (centre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE capteur ADD CONSTRAINT FK_5B4A1695F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
        $this->addSql('ALTER TABLE capteurp ADD CONSTRAINT FK_239B36CF231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_603499931B65292 FOREIGN KEY (employe_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_60349993463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)');
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5ECF231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
        $this->addSql('ALTER TABLE poubelle ADD CONSTRAINT FK_B5344EA3463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE capteur DROP FOREIGN KEY FK_5B4A1695F231B082');
        $this->addSql('ALTER TABLE capteurp DROP FOREIGN KEY FK_239B36CF231B082');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_603499931B65292');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_60349993463CD7C3');
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5ECF231B082');
        $this->addSql('ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3463CD7C3');
        $this->addSql('DROP TABLE capteur');
        $this->addSql('DROP TABLE capteurp');
        $this->addSql('DROP TABLE centre');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('DROP TABLE historique');
        $this->addSql('DROP TABLE poubelle');
    }
}
