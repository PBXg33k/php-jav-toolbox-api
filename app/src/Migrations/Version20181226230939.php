<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181226230939 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file DROP FOREIGN KEY FK_C32B7B813F7D58D2');
        $this->addSql('CREATE TABLE inode (id BIGINT NOT NULL, md5 VARCHAR(32) DEFAULT NULL, sha1 VARCHAR(40) DEFAULT NULL, sha512 VARCHAR(128) DEFAULT NULL, xxhash VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE file_hash');
        $this->addSql('DROP INDEX UNIQ_C32B7B813F7D58D2 ON jav_file');
        $this->addSql('INSERT INTO inode (id) SELECT DISTINCT inode FROM jav_file');
        $this->addSql('ALTER TABLE jav_file ADD inode_id BIGINT DEFAULT NULL, DROP hash_id, CHANGE title_id title_id INT DEFAULT NULL, CHANGE height height INT DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE fps fps DOUBLE PRECISION DEFAULT NULL, CHANGE codec codec VARCHAR(255) DEFAULT NULL, CHANGE consistent consistent TINYINT(1) DEFAULT NULL, CHANGE length length INT DEFAULT NULL, CHANGE bitrate bitrate INT DEFAULT NULL, CHANGE checked checked TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE jav_file SET inode_id = inode');
        $this->addSql('ALTER TABLE jav_file DROP inode');
        $this->addSql('ALTER TABLE jav_file ADD CONSTRAINT FK_C32B7B8171E72450 FOREIGN KEY (inode_id) REFERENCES inode (id)');
        $this->addSql('CREATE INDEX IDX_C32B7B8171E72450 ON jav_file (inode_id)');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT NULL, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file DROP FOREIGN KEY FK_C32B7B8171E72450');
        $this->addSql('CREATE TABLE file_hash (id INT AUTO_INCREMENT NOT NULL, md5 VARCHAR(32) NOT NULL COLLATE utf8mb4_unicode_ci, sha1 VARCHAR(40) NOT NULL COLLATE utf8mb4_unicode_ci, sha512 VARCHAR(128) NOT NULL COLLATE utf8mb4_unicode_ci, xxhash VARCHAR(32) NOT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE inode');
        $this->addSql('DROP INDEX IDX_C32B7B8171E72450 ON jav_file');
        $this->addSql('ALTER TABLE jav_file ADD hash_id INT DEFAULT NULL, ADD inode BIGINT UNSIGNED NOT NULL, CHANGE title_id title_id INT DEFAULT NULL, CHANGE height height INT DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE fps fps DOUBLE PRECISION DEFAULT \'NULL\', CHANGE codec codec VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE consistent consistent TINYINT(1) DEFAULT \'NULL\', CHANGE length length INT DEFAULT NULL, CHANGE bitrate bitrate INT DEFAULT NULL, CHANGE checked checked TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('UPDATE jav_file SET inode = inode_id');
        $this->addSql('ALTER TABLE jav_file DROP inode_id');
        $this->addSql('ALTER TABLE jav_file ADD CONSTRAINT FK_C32B7B813F7D58D2 FOREIGN KEY (hash_id) REFERENCES file_hash (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C32B7B813F7D58D2 ON jav_file (hash_id)');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
