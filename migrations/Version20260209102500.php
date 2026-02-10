<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260209102500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ON DELETE CASCADE to event-related foreign keys (registrations, sponsors, rating)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registrations DROP FOREIGN KEY FK_53DE51E7FD02F13');
        $this->addSql('ALTER TABLE registrations ADD CONSTRAINT FK_53DE51E7FD02F13 FOREIGN KEY (evenement_id) REFERENCES events (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE sponsors DROP FOREIGN KEY FK_9A31550F71F7E88B');
        $this->addSql('ALTER TABLE sponsors ADD CONSTRAINT FK_9A31550F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D889262271F7E88B');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D889262271F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registrations DROP FOREIGN KEY FK_53DE51E7FD02F13');
        $this->addSql('ALTER TABLE registrations ADD CONSTRAINT FK_53DE51E7FD02F13 FOREIGN KEY (evenement_id) REFERENCES events (id)');

        $this->addSql('ALTER TABLE sponsors DROP FOREIGN KEY FK_9A31550F71F7E88B');
        $this->addSql('ALTER TABLE sponsors ADD CONSTRAINT FK_9A31550F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');

        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D889262271F7E88B');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D889262271F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
    }
}
