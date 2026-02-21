<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260221130934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meet_participants DROP FOREIGN KEY `FK_MEET_PARTICIPANTS_MEET`');
        $this->addSql('ALTER TABLE meet_participants DROP FOREIGN KEY `FK_MEET_PARTICIPANTS_PARTICIPANT`');
        $this->addSql('DROP INDEX idx_meet_participants_meet ON meet_participants');
        $this->addSql('CREATE INDEX IDX_4D90C6BF3BBBF66 ON meet_participants (meet_id)');
        $this->addSql('DROP INDEX idx_meet_participants_participant ON meet_participants');
        $this->addSql('CREATE INDEX IDX_4D90C6BF9D1C3019 ON meet_participants (participant_id)');
        $this->addSql('ALTER TABLE meet_participants ADD CONSTRAINT `FK_MEET_PARTICIPANTS_MEET` FOREIGN KEY (meet_id) REFERENCES meet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meet_participants ADD CONSTRAINT `FK_MEET_PARTICIPANTS_PARTICIPANT` FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant ADD smtp_email VARCHAR(255) DEFAULT NULL, ADD smtp_app_password VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meet_participants DROP FOREIGN KEY FK_4D90C6BF3BBBF66');
        $this->addSql('ALTER TABLE meet_participants DROP FOREIGN KEY FK_4D90C6BF9D1C3019');
        $this->addSql('DROP INDEX idx_4d90c6bf9d1c3019 ON meet_participants');
        $this->addSql('CREATE INDEX IDX_MEET_PARTICIPANTS_PARTICIPANT ON meet_participants (participant_id)');
        $this->addSql('DROP INDEX idx_4d90c6bf3bbbf66 ON meet_participants');
        $this->addSql('CREATE INDEX IDX_MEET_PARTICIPANTS_MEET ON meet_participants (meet_id)');
        $this->addSql('ALTER TABLE meet_participants ADD CONSTRAINT FK_4D90C6BF3BBBF66 FOREIGN KEY (meet_id) REFERENCES meet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meet_participants ADD CONSTRAINT FK_4D90C6BF9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant DROP smtp_email, DROP smtp_app_password');
    }
}
