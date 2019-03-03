<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181218134811 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file ADD checked TINYINT(1) DEFAULT NULL, CHANGE hash_id hash_id INT DEFAULT NULL, CHANGE title_id title_id INT DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE fps fps DOUBLE PRECISION DEFAULT NULL, CHANGE codec codec VARCHAR(255) DEFAULT NULL, CHANGE consistent consistent TINYINT(1) DEFAULT NULL, CHANGE length length INT DEFAULT NULL, CHANGE bitrate bitrate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT NULL, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file DROP checked, CHANGE hash_id hash_id INT DEFAULT NULL, CHANGE title_id title_id INT DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE fps fps DOUBLE PRECISION DEFAULT \'NULL\', CHANGE codec codec VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE consistent consistent TINYINT(1) DEFAULT \'NULL\', CHANGE length length INT DEFAULT NULL, CHANGE bitrate bitrate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE model_alias CHANGE model_id model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE title CHANGE name_romaji name_romaji VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name_japanese name_japanese VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
