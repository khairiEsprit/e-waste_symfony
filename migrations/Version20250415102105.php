<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415102105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD image VARCHAR(255) DEFAULT NULL, DROP description, CHANGE created_at created_at VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE participation CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_AB55E24F71F7E88B ON participation (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD description LONGTEXT DEFAULT NULL, DROP image, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F71F7E88B');
        $this->addSql('DROP INDEX IDX_AB55E24F71F7E88B ON participation');
        $this->addSql('ALTER TABLE participation CHANGE event_id event_id INT NOT NULL');
    }
}
