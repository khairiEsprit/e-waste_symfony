<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405151405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE capteur ADD id_c INT AUTO_INCREMENT NOT NULL, DROP id, ADD PRIMARY KEY (id_c)');
        $this->addSql('ALTER TABLE capteur ADD CONSTRAINT FK_5B4A1695F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5B4A1695F231B082 ON capteur (poubelle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE capteur MODIFY id_c INT NOT NULL');
        $this->addSql('ALTER TABLE capteur DROP FOREIGN KEY FK_5B4A1695F231B082');
        $this->addSql('DROP INDEX UNIQ_5B4A1695F231B082 ON capteur');
        $this->addSql('DROP INDEX `primary` ON capteur');
        $this->addSql('ALTER TABLE capteur ADD id INT NOT NULL, DROP id_c');
    }
}
