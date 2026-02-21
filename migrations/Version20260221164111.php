<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260221164111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD target_audience VARCHAR(255) DEFAULT NULL, ADD required_level VARCHAR(255) DEFAULT NULL, ADD tags JSON DEFAULT NULL, DROP attendees_emails, CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user ADD profession VARCHAR(255) DEFAULT NULL, ADD experience_level VARCHAR(255) DEFAULT NULL, ADD skills JSON DEFAULT NULL, ADD interests JSON DEFAULT NULL, DROP last_name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD attendees_emails VARCHAR(500) DEFAULT NULL, DROP target_audience, DROP required_level, DROP tags, CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE user ADD last_name VARCHAR(255) NOT NULL, DROP profession, DROP experience_level, DROP skills, DROP interests');
    }
}
