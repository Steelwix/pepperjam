<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261103140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add software entries and their image relation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE software (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media ADD software_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6C3E1A7F5A2AA9A FOREIGN KEY (software_id) REFERENCES software (id)');
        $this->addSql('CREATE INDEX IDX_6C3E1A7F5A2AA9A ON media (software_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6C3E1A7F5A2AA9A');
        $this->addSql('DROP INDEX IDX_6C3E1A7F5A2AA9A ON media');
        $this->addSql('ALTER TABLE media DROP software_id');
        $this->addSql('DROP TABLE software');
    }
}
