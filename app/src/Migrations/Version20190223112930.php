<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190223112930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX idxcatno ON title');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT NULL, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jav_file CHANGE title_id title_id INT DEFAULT NULL, CHANGE inode_id inode_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE inode CHANGE md5 md5 VARCHAR(32) DEFAULT NULL, CHANGE sha1 sha1 VARCHAR(40) DEFAULT NULL, CHANGE sha512 sha512 VARCHAR(128) DEFAULT NULL, CHANGE xxhash xxhash VARCHAR(32) DEFAULT NULL, CHANGE checked checked TINYINT(1) DEFAULT NULL, CHANGE height height INT DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE fps fps DOUBLE PRECISION DEFAULT NULL, CHANGE codec codec VARCHAR(255) DEFAULT NULL, CHANGE consistent consistent TINYINT(1) DEFAULT NULL, CHANGE length length INT DEFAULT NULL, CHANGE bitrate bitrate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE inode CHANGE md5 md5 VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE sha1 sha1 VARCHAR(40) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE sha512 sha512 VARCHAR(128) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE xxhash xxhash VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE checked checked TINYINT(1) DEFAULT \'NULL\', CHANGE height height INT DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE fps fps DOUBLE PRECISION DEFAULT \'NULL\', CHANGE codec codec VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE consistent consistent TINYINT(1) DEFAULT \'NULL\', CHANGE length length INT DEFAULT NULL, CHANGE bitrate bitrate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE jav_file CHANGE title_id title_id INT DEFAULT NULL, CHANGE inode_id inode_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX idxcatno ON title (catalognumber)');
    }
}
