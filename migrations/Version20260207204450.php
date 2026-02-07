<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207204450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, stars INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D889262271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D889262271F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE events CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE registrations CHANGE montant_paye montant_paye NUMERIC(8, 2) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D889262271F7E88B');
        $this->addSql('DROP TABLE rating');
        $this->addSql('ALTER TABLE events CHANGE prix prix NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE registrations DROP FOREIGN KEY FK_53DE51E7FD02F13');
        $this->addSql('ALTER TABLE registrations CHANGE montant_paye montant_paye NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE sponsors DROP FOREIGN KEY FK_9A31550F71F7E88B');
    }
}
