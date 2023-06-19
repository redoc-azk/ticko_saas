<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230619105617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participants ADD scanned_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participants ADD CONSTRAINT FK_71697092EBBC642F FOREIGN KEY (scanned_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_71697092EBBC642F ON participants (scanned_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participants DROP FOREIGN KEY FK_71697092EBBC642F');
        $this->addSql('DROP INDEX IDX_71697092EBBC642F ON participants');
        $this->addSql('ALTER TABLE participants DROP scanned_by_id');
    }
}
