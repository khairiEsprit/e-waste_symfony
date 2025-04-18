<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418190939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE face_login_attempt (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, timestamp DATETIME NOT NULL, success TINYINT(1) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, confidence_score DOUBLE PRECISION DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, INDEX IDX_6DF5C03AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE face_login_attempt ADD CONSTRAINT FK_6DF5C03AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE capteur DROP FOREIGN KEY FK_5B4A1695F231B082
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE capteurp DROP FOREIGN KEY FK_239B36CF231B082
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat DROP FOREIGN KEY FK_603499931B65292
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat DROP FOREIGN KEY FK_60349993463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5ECF231B082
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F71F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F782F8B1AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D2780E95E18
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE avis
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE capteur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE capteurp
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE centre
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contrat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE demande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE historique
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE plannificationtache
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE poubelle
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tache
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE traitement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD is_face_recognition_enabled TINYINT(1) DEFAULT 0 NOT NULL, ADD face_embeddings LONGTEXT DEFAULT NULL, ADD face_photo_path VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, avis LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, note INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE capteur (id_c INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, distance_mesuree DOUBLE PRECISION NOT NULL, date_mesure DATETIME DEFAULT CURRENT_TIMESTAMP, portee_maximale DOUBLE PRECISION NOT NULL, precision_capteur DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_5B4A1695F231B082 (poubelle_id), PRIMARY KEY(id_c)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE capteurp (id INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, quantite DOUBLE PRECISION NOT NULL, date_m DATETIME NOT NULL, UNIQUE INDEX UNIQ_239B36CF231B082 (poubelle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE centre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, longitude DOUBLE PRECISION NOT NULL, altitude DOUBLE PRECISION NOT NULL, telephone VARCHAR(8) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, employe_id INT NOT NULL, centre_id INT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, signature_path VARCHAR(700) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_60349993463CD7C3 (centre_id), INDEX IDX_603499931B65292 (employe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, centre_id INT DEFAULT NULL, adresse VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email_utilisateur VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, message VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_2694D7A5463CD7C3 (centre_id), INDEX IDX_2694D7A5FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, remaining_places INT DEFAULT 0 NOT NULL, location VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE historique (id INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, date_evenement DATETIME NOT NULL, type_evenement VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, quantite_dechets DOUBLE PRECISION NOT NULL, INDEX IDX_EDBFD5ECF231B082 (poubelle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, zip_code VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, country VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, reminder_sent TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_AB55E24F71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE plannificationtache (id INT AUTO_INCREMENT NOT NULL, id_tache INT NOT NULL, priorite VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_limite DATE NOT NULL, INDEX IDX_AD0273F77D026145 (id_tache), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE poubelle (id INT AUTO_INCREMENT NOT NULL, centre_id INT DEFAULT NULL, adresse VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, niveau INT NOT NULL, etat VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_installation DATE NOT NULL, hauteur_totale INT NOT NULL, INDEX IDX_B5344EA3463CD7C3 (centre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, id_centre INT NOT NULL, id_employe INT NOT NULL, altitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, message VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, etat VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, demande_id INT NOT NULL, status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_traitement DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, commentaire LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_2A356D2780E95E18 (demande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE capteur ADD CONSTRAINT FK_5B4A1695F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE capteurp ADD CONSTRAINT FK_239B36CF231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat ADD CONSTRAINT FK_603499931B65292 FOREIGN KEY (employe_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat ADD CONSTRAINT FK_60349993463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5ECF231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plannificationtache ADD CONSTRAINT FK_AD0273F782F8B1AC FOREIGN KEY (id_tache) REFERENCES tache (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE poubelle ADD CONSTRAINT FK_B5344EA3463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE traitement ADD CONSTRAINT FK_2A356D2780E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE face_login_attempt DROP FOREIGN KEY FK_6DF5C03AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE face_login_attempt
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP is_face_recognition_enabled, DROP face_embeddings, DROP face_photo_path
        SQL);
    }
}
