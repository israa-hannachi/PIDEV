<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260220231500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add resume_ai field to cours';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours ADD resume_ai LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours DROP resume_ai');
    }
}
