<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218181142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aimodel_adjustment (id INT AUTO_INCREMENT NOT NULL, factor_type VARCHAR(255) NOT NULL, factor_value VARCHAR(255) NOT NULL, adjustment_multiplier DOUBLE PRECISION NOT NULL, sample_size INT NOT NULL, last_updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aiprediction (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, prediction_type VARCHAR(255) NOT NULL, predicted_value DOUBLE PRECISION NOT NULL, actual_value DOUBLE PRECISION DEFAULT NULL, confidence DOUBLE PRECISION NOT NULL, factors JSON NOT NULL, prediction_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', accuracy_percentage DOUBLE PRECISION DEFAULT NULL, evaluated TINYINT(1) NOT NULL, INDEX IDX_3608490B71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, actif TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, module_id INT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, fichier_contenu VARCHAR(255) DEFAULT NULL, duree INT NOT NULL, ordre INT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, actif TINYINT(1) NOT NULL, INDEX IDX_FDCA8C9CAFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_chats (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT DEFAULT NULL, sender VARCHAR(50) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, visibility VARCHAR(50) DEFAULT \'public\' NOT NULL, likes INT DEFAULT NULL, metadata JSON DEFAULT NULL, is_ai_generated TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_6C73A92371F7E88B (event_id), INDEX IDX_6C73A923A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_posters (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, image_url VARCHAR(500) NOT NULL, prompt LONGTEXT NOT NULL, style VARCHAR(50) NOT NULL, generated_by VARCHAR(50) NOT NULL, generated_at DATETIME NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, download_count INT DEFAULT NULL, metadata JSON DEFAULT NULL, UNIQUE INDEX UNIQ_CDC7849871F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, capacite INT DEFAULT 50 NOT NULL, inscrits INT DEFAULT 0 NOT NULL, image VARCHAR(300) DEFAULT NULL, categorie VARCHAR(50) NOT NULL, prix NUMERIC(8, 2) DEFAULT \'0\' NOT NULL, lieu VARCHAR(250) NOT NULL, latitude NUMERIC(10, 6) DEFAULT NULL, longitude NUMERIC(10, 6) DEFAULT NULL, statut VARCHAR(50) NOT NULL, time_zone VARCHAR(50) DEFAULT \'UTC\' NOT NULL, is_recurring TINYINT(1) DEFAULT 0 NOT NULL, recurrence_frequency VARCHAR(50) DEFAULT NULL, recurrence_count INT DEFAULT NULL, attendees_emails VARCHAR(500) DEFAULT NULL, organizer_email VARCHAR(255) DEFAULT NULL, ical_id VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_5387574AFF7747B4 (titre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE module (id INT AUTO_INCREMENT NOT NULL, categorie_id INT NOT NULL, titre VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, duree INT NOT NULL, niveau VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, actif TINYINT(1) NOT NULL, INDEX IDX_C242628BCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, stars INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D889262271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recommendation_cache (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, match_score DOUBLE PRECISION NOT NULL, factor_scores JSON NOT NULL, explanations JSON NOT NULL, computed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_valid TINYINT(1) NOT NULL, INDEX IDX_8836B028A76ED395 (user_id), INDEX IDX_8836B02871F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registrations (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, visitor_name VARCHAR(100) NOT NULL, visitor_email VARCHAR(180) NOT NULL, date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, statut VARCHAR(50) NOT NULL, presence TINYINT(1) DEFAULT 0 NOT NULL, mode_paiement VARCHAR(50) NOT NULL, montant_paye NUMERIC(8, 2) DEFAULT \'0\' NOT NULL, paiement_statut VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_53DE51E7FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sponsors (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, type VARCHAR(50) NOT NULL, montant NUMERIC(10, 2) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, contact_personne VARCHAR(150) DEFAULT NULL, contact_email VARCHAR(180) DEFAULT NULL, contact_telephone VARCHAR(20) DEFAULT NULL, date_creation DATETIME NOT NULL, INDEX IDX_9A31550F71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, profile_picture VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_event_interaction (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT NOT NULL, interaction_type VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', duration INT DEFAULT NULL, metadata JSON DEFAULT NULL, INDEX IDX_641B950CA76ED395 (user_id), INDEX IDX_641B950C71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_preference_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, preferred_categories JSON NOT NULL, preferred_topics JSON NOT NULL, preferred_difficulty VARCHAR(255) DEFAULT NULL, preferred_days JSON NOT NULL, activity_score DOUBLE PRECISION NOT NULL, profile_completeness DOUBLE PRECISION NOT NULL, last_computed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4DFF43E4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aiprediction ADD CONSTRAINT FK_3608490B71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CAFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE event_chats ADD CONSTRAINT FK_6C73A92371F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE event_chats ADD CONSTRAINT FK_6C73A923A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event_posters ADD CONSTRAINT FK_CDC7849871F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C242628BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D889262271F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE recommendation_cache ADD CONSTRAINT FK_8836B028A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recommendation_cache ADD CONSTRAINT FK_8836B02871F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE registrations ADD CONSTRAINT FK_53DE51E7FD02F13 FOREIGN KEY (evenement_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE sponsors ADD CONSTRAINT FK_9A31550F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE user_event_interaction ADD CONSTRAINT FK_641B950CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_event_interaction ADD CONSTRAINT FK_641B950C71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE user_preference_profile ADD CONSTRAINT FK_4DFF43E4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aiprediction DROP FOREIGN KEY FK_3608490B71F7E88B');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CAFC2B591');
        $this->addSql('ALTER TABLE event_chats DROP FOREIGN KEY FK_6C73A92371F7E88B');
        $this->addSql('ALTER TABLE event_chats DROP FOREIGN KEY FK_6C73A923A76ED395');
        $this->addSql('ALTER TABLE event_posters DROP FOREIGN KEY FK_CDC7849871F7E88B');
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C242628BCF5E72D');
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D889262271F7E88B');
        $this->addSql('ALTER TABLE recommendation_cache DROP FOREIGN KEY FK_8836B028A76ED395');
        $this->addSql('ALTER TABLE recommendation_cache DROP FOREIGN KEY FK_8836B02871F7E88B');
        $this->addSql('ALTER TABLE registrations DROP FOREIGN KEY FK_53DE51E7FD02F13');
        $this->addSql('ALTER TABLE sponsors DROP FOREIGN KEY FK_9A31550F71F7E88B');
        $this->addSql('ALTER TABLE user_event_interaction DROP FOREIGN KEY FK_641B950CA76ED395');
        $this->addSql('ALTER TABLE user_event_interaction DROP FOREIGN KEY FK_641B950C71F7E88B');
        $this->addSql('ALTER TABLE user_preference_profile DROP FOREIGN KEY FK_4DFF43E4A76ED395');
        $this->addSql('DROP TABLE aimodel_adjustment');
        $this->addSql('DROP TABLE aiprediction');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE event_chats');
        $this->addSql('DROP TABLE event_posters');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE module');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE recommendation_cache');
        $this->addSql('DROP TABLE registrations');
        $this->addSql('DROP TABLE sponsors');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_event_interaction');
        $this->addSql('DROP TABLE user_preference_profile');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
