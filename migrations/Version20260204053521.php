<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204053521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD latitude NUMERIC(10, 6) DEFAULT NULL, ADD longitude NUMERIC(10, 6) DEFAULT NULL, CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE registrations CHANGE montant_paye montant_paye NUMERIC(8, 2) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events DROP latitude, DROP longitude, CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE registrations DROP FOREIGN KEY FK_53DE51E7FD02F13');
        $this->addSql('ALTER TABLE registrations CHANGE montant_paye montant_paye NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE sponsors DROP FOREIGN KEY FK_9A31550F71F7E88B');
    }
}
