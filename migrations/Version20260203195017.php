<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203195017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, capacite INT DEFAULT 50 NOT NULL, inscrits INT DEFAULT 0 NOT NULL, image VARCHAR(300) DEFAULT NULL, categorie VARCHAR(50) NOT NULL, prix NUMERIC(8, 2) DEFAULT 0 NOT NULL, lieu VARCHAR(250) NOT NULL, statut VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_5387574AFF7747B4 (titre), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE registrations (id INT AUTO_INCREMENT NOT NULL, visitor_name VARCHAR(100) NOT NULL, visitor_email VARCHAR(180) NOT NULL, date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, statut VARCHAR(50) NOT NULL, presence TINYINT DEFAULT 0 NOT NULL, mode_paiement VARCHAR(50) NOT NULL, montant_paye NUMERIC(8, 2) DEFAULT 0 NOT NULL, paiement_statut VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, evenement_id INT NOT NULL, INDEX IDX_53DE51E7FD02F13 (evenement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sponsors (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, type VARCHAR(50) NOT NULL, montant NUMERIC(10, 2) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, contact_personne VARCHAR(150) DEFAULT NULL, contact_email VARCHAR(180) DEFAULT NULL, contact_telephone VARCHAR(20) DEFAULT NULL, date_creation DATETIME NOT NULL, event_id INT NOT NULL, INDEX IDX_9A31550F71F7E88B (event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE registrations ADD CONSTRAINT FK_53DE51E7FD02F13 FOREIGN KEY (evenement_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE sponsors ADD CONSTRAINT FK_9A31550F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registrations DROP FOREIGN KEY FK_53DE51E7FD02F13');
        $this->addSql('ALTER TABLE sponsors DROP FOREIGN KEY FK_9A31550F71F7E88B');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE registrations');
        $this->addSql('DROP TABLE sponsors');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
