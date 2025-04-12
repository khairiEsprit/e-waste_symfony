<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250412164929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE center (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, id_utilisateur INT DEFAULT NULL, id_centre INT DEFAULT NULL, adresse VARCHAR(255) NOT NULL, email_utilisateur VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_2694D7A550EAE44 (id_utilisateur), INDEX IDX_2694D7A5E0975F98 (id_centre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, demande_id INT NOT NULL, status VARCHAR(50) NOT NULL, date_traitement DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, commentaire LONGTEXT DEFAULT NULL, INDEX IDX_2A356D2780E95E18 (demande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A550EAE44 FOREIGN KEY (id_utilisateur) REFERENCES user (id)');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5E0975F98 FOREIGN KEY (id_centre) REFERENCES center (id)');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D2780E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A550EAE44');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5E0975F98');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D2780E95E18');
        $this->addSql('DROP TABLE center');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE traitement');
        $this->addSql('DROP TABLE user');
    }
}
