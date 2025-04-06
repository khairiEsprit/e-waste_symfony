<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405162235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE traitement ADD demande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D2780E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id)');
        $this->addSql('CREATE INDEX IDX_2A356D2780E95E18 ON traitement (demande_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D2780E95E18');
        $this->addSql('DROP INDEX IDX_2A356D2780E95E18 ON traitement');
        $this->addSql('ALTER TABLE traitement DROP demande_id');
    }
}
