<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260221230329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD attendees_emails LONGTEXT DEFAULT NULL, ADD notes_interne LONGTEXT DEFAULT NULL, DROP ical_id, CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD ical_id VARCHAR(100) DEFAULT NULL, DROP attendees_emails, DROP notes_interne, CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
