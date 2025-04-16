<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416151648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE centre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, longitude DOUBLE PRECISION NOT NULL, altitude DOUBLE PRECISION NOT NULL, telephone VARCHAR(8) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, employe_id INT NOT NULL, centre_id INT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, signature_path VARCHAR(700) NOT NULL, INDEX IDX_603499931B65292 (employe_id), INDEX IDX_60349993463CD7C3 (centre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_603499931B65292 FOREIGN KEY (employe_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_60349993463CD7C3 FOREIGN KEY (centre_id) REFERENCES centre (id)');
        $this->addSql('ALTER TABLE engagement_prediction DROP FOREIGN KEY FK_D5D8C39BA76ED395');
        $this->addSql('DROP INDEX idx_d5d8c39ba76ed395 ON engagement_prediction');
        $this->addSql('CREATE INDEX IDX_FB54117DA76ED395 ON engagement_prediction (user_id)');
        $this->addSql('ALTER TABLE engagement_prediction ADD CONSTRAINT FK_D5D8C39BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F782F8B1AC');
        $this->addSql('DROP INDEX idx_ad0273f782f8b1ac ON plannificationtache');
        $this->addSql('CREATE INDEX IDX_AD0273F77D026145 ON plannificationtache (id_tache)');
        $this->addSql('ALTER TABLE plannificationtache ADD CONSTRAINT FK_AD0273F782F8B1AC FOREIGN KEY (id_tache) REFERENCES tache (id)');
        $this->addSql('ALTER TABLE user DROP total_points, DROP last_reward_at');
        $this->addSql('ALTER TABLE user_group_tax ADD CONSTRAINT FK_DF3DFEBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_group_tax ADD CONSTRAINT FK_DF3DFEBFE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39AA76ED395');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39AE466ACA1');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39A9D32F035');
        $this->addSql('DROP INDEX idx_d5d8c39aa76ed395 ON user_reward');
        $this->addSql('CREATE INDEX IDX_2B83696EA76ED395 ON user_reward (user_id)');
        $this->addSql('DROP INDEX idx_d5d8c39ae466aca1 ON user_reward');
        $this->addSql('CREATE INDEX IDX_2B83696EE466ACA1 ON user_reward (reward_id)');
        $this->addSql('DROP INDEX idx_d5d8c39a9d32f035 ON user_reward');
        $this->addSql('CREATE INDEX IDX_2B83696E9D32F035 ON user_reward (action_id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39AE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39A9D32F035 FOREIGN KEY (action_id) REFERENCES gamification_action (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_603499931B65292');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_60349993463CD7C3');
        $this->addSql('DROP TABLE centre');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('ALTER TABLE engagement_prediction DROP FOREIGN KEY FK_FB54117DA76ED395');
        $this->addSql('DROP INDEX idx_fb54117da76ed395 ON engagement_prediction');
        $this->addSql('CREATE INDEX IDX_D5D8C39BA76ED395 ON engagement_prediction (user_id)');
        $this->addSql('ALTER TABLE engagement_prediction ADD CONSTRAINT FK_FB54117DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F77D026145');
        $this->addSql('DROP INDEX idx_ad0273f77d026145 ON plannificationtache');
        $this->addSql('CREATE INDEX IDX_AD0273F782F8B1AC ON plannificationtache (id_tache)');
        $this->addSql('ALTER TABLE plannificationtache ADD CONSTRAINT FK_AD0273F77D026145 FOREIGN KEY (id_tache) REFERENCES tache (id)');
        $this->addSql('ALTER TABLE user ADD total_points INT DEFAULT 0 NOT NULL, ADD last_reward_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_group_tax DROP FOREIGN KEY FK_DF3DFEBA76ED395');
        $this->addSql('ALTER TABLE user_group_tax DROP FOREIGN KEY FK_DF3DFEBFE54D947');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696EA76ED395');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696EE466ACA1');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696E9D32F035');
        $this->addSql('DROP INDEX idx_2b83696e9d32f035 ON user_reward');
        $this->addSql('CREATE INDEX IDX_D5D8C39A9D32F035 ON user_reward (action_id)');
        $this->addSql('DROP INDEX idx_2b83696ea76ed395 ON user_reward');
        $this->addSql('CREATE INDEX IDX_D5D8C39AA76ED395 ON user_reward (user_id)');
        $this->addSql('DROP INDEX idx_2b83696ee466aca1 ON user_reward');
        $this->addSql('CREATE INDEX IDX_D5D8C39AE466ACA1 ON user_reward (reward_id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696EE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696E9D32F035 FOREIGN KEY (action_id) REFERENCES gamification_action (id)');
    }
}
