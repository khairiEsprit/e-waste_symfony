<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add reminder_sent column to participation table
 */
final class Version20250414132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reminder_sent column to participation table';
    }

    public function up(Schema $schema): void
    {
        // Check if the column exists first to avoid errors
        $table = $schema->getTable('participation');
        if (!$table->hasColumn('reminder_sent')) {
            $this->addSql('ALTER TABLE participation ADD reminder_sent TINYINT(1) DEFAULT 0 NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE participation DROP reminder_sent');
    }
}
