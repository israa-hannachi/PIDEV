<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220171100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add visibility scheduling fields to cours (visible, visible_from)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours ADD visible TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE cours ADD visible_from DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours DROP visible_from');
        $this->addSql('ALTER TABLE cours DROP visible');
    }
}
