<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416142309 extends AbstractMigration
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
        $this->addSql('ALTER TABLE engagement_prediction DROP FOREIGN KEY FK_D5D8C39BA76ED395');
        $this->addSql('DROP INDEX idx_d5d8c39ba76ed395 ON engagement_prediction');
        $this->addSql('CREATE INDEX IDX_FB54117DA76ED395 ON engagement_prediction (user_id)');
        $this->addSql('ALTER TABLE engagement_prediction ADD CONSTRAINT FK_D5D8C39BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user DROP total_points, DROP last_reward_at');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39A9D32F035');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39AA76ED395');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39AE466ACA1');
        $this->addSql('DROP INDEX idx_d5d8c39aa76ed395 ON user_reward');
        $this->addSql('CREATE INDEX IDX_2B83696EA76ED395 ON user_reward (user_id)');
        $this->addSql('DROP INDEX idx_d5d8c39ae466aca1 ON user_reward');
        $this->addSql('CREATE INDEX IDX_2B83696EE466ACA1 ON user_reward (reward_id)');
        $this->addSql('DROP INDEX idx_d5d8c39a9d32f035 ON user_reward');
        $this->addSql('CREATE INDEX IDX_2B83696E9D32F035 ON user_reward (action_id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39A9D32F035 FOREIGN KEY (action_id) REFERENCES gamification_action (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39AE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plannificationtache DROP FOREIGN KEY FK_AD0273F77D026145');
        $this->addSql('DROP TABLE plannificationtache');
        $this->addSql('DROP TABLE tache');
        $this->addSql('ALTER TABLE engagement_prediction DROP FOREIGN KEY FK_FB54117DA76ED395');
        $this->addSql('DROP INDEX idx_fb54117da76ed395 ON engagement_prediction');
        $this->addSql('CREATE INDEX IDX_D5D8C39BA76ED395 ON engagement_prediction (user_id)');
        $this->addSql('ALTER TABLE engagement_prediction ADD CONSTRAINT FK_FB54117DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD total_points INT DEFAULT 0 NOT NULL, ADD last_reward_at DATETIME DEFAULT NULL');
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
