<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413105642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD avis LONGTEXT NOT NULL, DROP id_utilisateur, DROP id_centre, DROP description, DROP created_at, CHANGE nom nom VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE event CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE image_url image_url VARCHAR(255) DEFAULT NULL, CHANGE remaining_places remaining_places INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD id_utilisateur INT DEFAULT NULL, ADD id_centre INT DEFAULT NULL, ADD description VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP avis, CHANGE nom nom VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE event CHANGE description description VARCHAR(255) NOT NULL, CHANGE image_url image_url VARCHAR(255) NOT NULL, CHANGE remaining_places remaining_places INT NOT NULL');
    }
}
