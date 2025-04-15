<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create gamification tables';
    }

    public function up(Schema $schema): void
    {
        // Create gamification_action table
        $this->addSql('CREATE TABLE gamification_action (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            points INT NOT NULL,
            description LONGTEXT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create reward table
        $this->addSql('CREATE TABLE reward (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(20) NOT NULL,
            points_required INT NOT NULL,
            description LONGTEXT NOT NULL,
            image_url VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create user_reward table
        $this->addSql('CREATE TABLE user_reward (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            reward_id INT DEFAULT NULL,
            action_id INT DEFAULT NULL,
            points INT NOT NULL,
            earned_at DATETIME NOT NULL,
            INDEX idx_user_reward_points (points),
            INDEX IDX_D5D8C39AA76ED395 (user_id),
            INDEX IDX_D5D8C39AE466ACA1 (reward_id),
            INDEX IDX_D5D8C39A9D32F035 (action_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create engagement_prediction table
        $this->addSql('CREATE TABLE engagement_prediction (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            score DOUBLE PRECISION NOT NULL,
            predicted_at DATETIME NOT NULL,
            ai_response LONGTEXT DEFAULT NULL,
            INDEX IDX_D5D8C39BA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39AE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_D5D8C39A9D32F035 FOREIGN KEY (action_id) REFERENCES gamification_action (id)');
        $this->addSql('ALTER TABLE engagement_prediction ADD CONSTRAINT FK_D5D8C39BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints first
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39AA76ED395');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39AE466ACA1');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_D5D8C39A9D32F035');
        $this->addSql('ALTER TABLE engagement_prediction DROP FOREIGN KEY FK_D5D8C39BA76ED395');

        // Drop tables
        $this->addSql('DROP TABLE gamification_action');
        $this->addSql('DROP TABLE reward');
        $this->addSql('DROP TABLE user_reward');
        $this->addSql('DROP TABLE engagement_prediction');
    }
}
