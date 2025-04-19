<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419162503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, avis LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, note INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, user VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, centre_id INT DEFAULT NULL, adresse VARCHAR(255) NOT NULL, email_utilisateur VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_2694D7A5FB88E14F (utilisateur_id), INDEX IDX_2694D7A5463CD7C3 (centre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE engagement_prediction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, score DOUBLE PRECISION NOT NULL, predicted_at DATETIME NOT NULL, ai_response LONGTEXT DEFAULT NULL, INDEX IDX_FB54117DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, remaining_places INT DEFAULT 0 NOT NULL, location VARCHAR(255) NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE face_login_attempt (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, timestamp DATETIME NOT NULL, success TINYINT(1) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, confidence_score DOUBLE PRECISION DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, INDEX IDX_6DF5C03AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gamification_action (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, points INT NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, city VARCHAR(255) NOT NULL, zip_code VARCHAR(20) NOT NULL, country VARCHAR(255) NOT NULL, reminder_sent TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_AB55E24F71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE plannificationtache (id INT AUTO_INCREMENT NOT NULL, id_tache INT NOT NULL, priorite VARCHAR(255) NOT NULL, date_limite DATE NOT NULL, INDEX IDX_AD0273F77D026145 (id_tache), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reward (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL, points_required INT NOT NULL, description LONGTEXT NOT NULL, image_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, id_centre INT NOT NULL, id_employe INT NOT NULL, altitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, message VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, demande_id INT NOT NULL, status VARCHAR(50) NOT NULL, date_traitement DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, commentaire LONGTEXT DEFAULT NULL, INDEX IDX_2A356D2780E95E18 (demande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, birthdate DATE DEFAULT NULL, profile_image VARCHAR(255) DEFAULT NULL, is_face_recognition_enabled TINYINT(1) DEFAULT 0 NOT NULL, face_embeddings LONGTEXT DEFAULT NULL, face_photo_path VARCHAR(255) DEFAULT NULL, password VARCHAR(100) NOT NULL, email VARCHAR(100) DEFAULT NULL, active TINYINT(1) NOT NULL, freeze TINYINT(1) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, language VARCHAR(3) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_group_tax (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_DF3DFEBA76ED395 (user_id), INDEX IDX_DF3DFEBFE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', UNIQUE INDEX UNIQ_8F02BF9D5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_reward (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, reward_id INT DEFAULT NULL, action_id INT DEFAULT NULL, points INT NOT NULL, earned_at DATETIME NOT NULL, INDEX IDX_2B83696EA76ED395 (user_id), INDEX IDX_2B83696EE466ACA1 (reward_id), INDEX IDX_2B83696E9D32F035 (action_id), INDEX idx_user_reward_points (points), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE engagement_prediction ADD CONSTRAINT FK_FB54117DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE face_login_attempt ADD CONSTRAINT FK_6DF5C03AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plannificationtache ADD CONSTRAINT FK_AD0273F77D026145 FOREIGN KEY (id_tache) REFERENCES tache (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE traitement ADD CONSTRAINT FK_2A356D2780E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_tax ADD CONSTRAINT FK_DF3DFEBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_tax ADD CONSTRAINT FK_DF3DFEBFE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696EE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696E9D32F035 FOREIGN KEY (action_id) REFERENCES gamification_action (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat ADD CONSTRAINT FK_603499931B65292 FOREIGN KEY (employe_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat ADD CONSTRAINT FK_60349993463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5ECF231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE poubelle ADD CONSTRAINT FK_B5344EA3463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat DROP FOREIGN KEY FK_603499931B65292
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE engagement_prediction DROP FOREIGN KEY FK_FB54117DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE face_login_attempt DROP FOREIGN KEY FK_6DF5C03AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F71F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F77D026145
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D2780E95E18
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_tax DROP FOREIGN KEY FK_DF3DFEBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group_tax DROP FOREIGN KEY FK_DF3DFEBFE54D947
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696EA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696EE466ACA1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696E9D32F035
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE avis
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE chat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE demande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE engagement_prediction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE face_login_attempt
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gamification_action
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE plannificationtache
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reward
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tache
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE traitement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_group_tax
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_group
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_reward
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contrat DROP FOREIGN KEY FK_60349993463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5ECF231B082
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3463CD7C3
        SQL);
    }
}
