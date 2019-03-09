<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190109153641 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT NULL, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inode ADD checked TINYINT(1) DEFAULT NULL, ADD height INT DEFAULT NULL, ADD width INT DEFAULT NULL, ADD fps DOUBLE PRECISION DEFAULT NULL, ADD codec VARCHAR(255) DEFAULT NULL, ADD consistent TINYINT(1) DEFAULT NULL, ADD meta LONGBLOB DEFAULT NULL, ADD length INT DEFAULT NULL, ADD bitrate INT DEFAULT NULL, ADD filesize BIGINT UNSIGNED NOT NULL, ADD processed TINYINT(1) NOT NULL, CHANGE md5 md5 VARCHAR(32) DEFAULT NULL, CHANGE sha1 sha1 VARCHAR(40) DEFAULT NULL, CHANGE sha512 sha512 VARCHAR(128) DEFAULT NULL, CHANGE xxhash xxhash VARCHAR(32) DEFAULT NULL');
        // Migrate data
        $this->addSql('UPDATE inode i, jav_file f SET i.checked = f.checked, i.height = f.height, i.width = f.width, i.fps = f.fps, i.codec = f.codec, i.consistent = f.consistent, i.meta = f.meta, i.length = f.length, i.bitrate = f.bitrate, i.filesize = f.filesize, i.processed = f.processed WHERE i.id = f.inode_id');

        $this->addSql('ALTER TABLE jav_file DROP filesize, DROP processed, DROP height, DROP width, DROP fps, DROP codec, DROP consistent, DROP meta, DROP length, DROP bitrate, DROP checked, CHANGE title_id title_id INT DEFAULT NULL, CHANGE inode_id inode_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file ADD filesize BIGINT UNSIGNED NOT NULL, ADD processed TINYINT(1) NOT NULL, ADD height INT DEFAULT NULL, ADD width INT DEFAULT NULL, ADD fps DOUBLE PRECISION DEFAULT \'NULL\', ADD codec VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, ADD consistent TINYINT(1) DEFAULT \'NULL\', ADD meta LONGBLOB DEFAULT NULL, ADD length INT DEFAULT NULL, ADD bitrate INT DEFAULT NULL, ADD checked TINYINT(1) DEFAULT \'NULL\', CHANGE title_id title_id INT DEFAULT NULL, CHANGE inode_id inode_id BIGINT DEFAULT NULL');
        // migrate data
        $this->addSql('UPDATE inode i, jav_file f SET f.checked = i.checked, f.height = i.height, f.width = i.width, f.fps = i.fps, f.codec = i.codec, f.consistent = i.consistent, f.meta = i.meta, f.length = i.length, f.bitrate = i.bitrate, f.filesize = i.filesize, f.processed = i.processed WHERE i.id = f.inode_id');

        $this->addSql('ALTER TABLE inode DROP checked, DROP height, DROP width, DROP fps, DROP codec, DROP consistent, DROP meta, DROP length, DROP bitrate, DROP filesize, DROP processed, CHANGE md5 md5 VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE sha1 sha1 VARCHAR(40) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE sha512 sha512 VARCHAR(128) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE xxhash xxhash VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
