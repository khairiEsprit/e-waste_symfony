<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405144908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE capteur (id INT AUTO_INCREMENT NOT NULL, poubelle_id INT NOT NULL, distance_mesuree DOUBLE PRECISION NOT NULL, date_mesure DATETIME DEFAULT NULL, portee_maximale DOUBLE PRECISION NOT NULL, precision_capteur DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_5B4A1695F231B082 (poubelle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE capteur ADD CONSTRAINT FK_5B4A1695F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
        $this->addSql('ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3463CD7C3');
        $this->addSql('DROP INDEX IDX_B5344EA3463CD7C3 ON poubelle');
        $this->addSql('ALTER TABLE poubelle CHANGE id_centre centre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE poubelle ADD CONSTRAINT FK_B5344EA3463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)');
        $this->addSql('CREATE INDEX IDX_B5344EA3463CD7C3 ON poubelle (centre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE capteur DROP FOREIGN KEY FK_5B4A1695F231B082');
        $this->addSql('DROP TABLE capteur');
        $this->addSql('ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3463CD7C3');
        $this->addSql('DROP INDEX IDX_B5344EA3463CD7C3 ON poubelle');
        $this->addSql('ALTER TABLE poubelle CHANGE centre_id id_centre INT DEFAULT NULL');
        $this->addSql('ALTER TABLE poubelle ADD CONSTRAINT FK_B5344EA3463CD7C3 FOREIGN KEY (id_centre) REFERENCES centre (id)');
        $this->addSql('CREATE INDEX IDX_B5344EA3463CD7C3 ON poubelle (id_centre)');
    }
}
