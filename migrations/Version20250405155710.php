<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405155710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE capteurp (id INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, quantite DOUBLE PRECISION NOT NULL, date_m DATETIME NOT NULL, UNIQUE INDEX UNIQ_239B36CF231B082 (poubelle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE capteurp ADD CONSTRAINT FK_239B36CF231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE capteurp DROP FOREIGN KEY FK_239B36CF231B082');
        $this->addSql('DROP TABLE capteurp');
    }
}
