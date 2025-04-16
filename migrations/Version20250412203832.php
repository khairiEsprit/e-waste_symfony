<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250412203832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE plannificationtache (id INT AUTO_INCREMENT NOT NULL, id_tache INT NOT NULL, priorite VARCHAR(255) NOT NULL, date_limite DATE NOT NULL, INDEX IDX_AD0273F77D026145 (id_tache), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, id_centre INT NOT NULL, id_employe INT NOT NULL, altitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, message VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE plannificationtache ADD CONSTRAINT FK_AD0273F77D026145 FOREIGN KEY (id_tache) REFERENCES tache (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F77D026145');
        $this->addSql('DROP TABLE plannificationtache');
        $this->addSql('DROP TABLE tache');
    }
}
