<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407195034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F782F8B1AC');
        $this->addSql('DROP INDEX IDX_AD0273F782F8B1AC ON plannificationtache');
        $this->addSql('ALTER TABLE plannificationtache CHANGE id_tache_id id_tache INT NOT NULL');
        $this->addSql('ALTER TABLE plannificationtache ADD CONSTRAINT FK_AD0273F77D026145 FOREIGN KEY (id_tache) REFERENCES tache (id)');
        $this->addSql('CREATE INDEX IDX_AD0273F77D026145 ON plannificationtache (id_tache)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F77D026145');
        $this->addSql('DROP INDEX IDX_AD0273F77D026145 ON plannificationtache');
        $this->addSql('ALTER TABLE plannificationtache CHANGE id_tache id_tache_id INT NOT NULL');
        $this->addSql('ALTER TABLE plannificationtache ADD CONSTRAINT FK_AD0273F782F8B1AC FOREIGN KEY (id_tache_id) REFERENCES tache (id)');
        $this->addSql('CREATE INDEX IDX_AD0273F782F8B1AC ON plannificationtache (id_tache_id)');
    }
}
