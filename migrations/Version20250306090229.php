<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306090229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_list ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE task_list ADD CONSTRAINT FK_377B6C63A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_377B6C63A76ED395 ON task_list (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_list DROP CONSTRAINT FK_377B6C63A76ED395');
        $this->addSql('DROP INDEX IDX_377B6C63A76ED395');
        $this->addSql('ALTER TABLE task_list DROP user_id');
    }
}
