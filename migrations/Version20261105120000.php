<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261105120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add manual account activation for users';
    }

    public function up(Schema $schema): void
    {
        // Preserve access for accounts that already exist; new accounts default to disabled.
        $this->addSql('ALTER TABLE users ADD enabled TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE users ALTER enabled SET DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP enabled');
    }
}
