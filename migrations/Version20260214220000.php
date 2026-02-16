<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260214220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE module ADD cree_par_admin TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE cours ADD cree_par_admin TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours DROP cree_par_admin');
        $this->addSql('ALTER TABLE module DROP cree_par_admin');
    }
}
