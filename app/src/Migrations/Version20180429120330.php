<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180429120330 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file CHANGE hash_id hash_id INT DEFAULT NULL, CHANGE title_id title_id INT DEFAULT NULL, CHANGE inode inode BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT NULL, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file CHANGE hash_id hash_id INT DEFAULT NULL, CHANGE title_id title_id INT DEFAULT NULL, CHANGE inode inode BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
