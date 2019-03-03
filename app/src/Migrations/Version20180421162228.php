<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180421162228 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_hash (id INT AUTO_INCREMENT NOT NULL, md5 VARCHAR(32) NOT NULL, sha1 VARCHAR(40) NOT NULL, sha512 VARCHAR(128) NOT NULL, xxhash VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, uri VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_model (image_id INT NOT NULL, model_id INT NOT NULL, INDEX IDX_C0D0F6703DA5256D (image_id), INDEX IDX_C0D0F6707975B7E7 (model_id), PRIMARY KEY(image_id, model_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jav_file (id INT AUTO_INCREMENT NOT NULL, hash_id INT DEFAULT NULL, title_id INT DEFAULT NULL, part INT NOT NULL, filename VARCHAR(255) NOT NULL, filesize BIGINT UNSIGNED NOT NULL, processed TINYINT(1) NOT NULL, path LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_C32B7B813F7D58D2 (hash_id), INDEX IDX_C32B7B81A9F87BD (title_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE model (id INT AUTO_INCREMENT NOT NULL, name_romaji VARCHAR(255) NOT NULL, name_japanese VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE model_alias (id INT AUTO_INCREMENT NOT NULL, model_id INT DEFAULT NULL, name_romaji VARCHAR(255) NOT NULL, name_japanese VARCHAR(255) NOT NULL, INDEX IDX_DF2F4B637975B7E7 (model_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE title (id INT AUTO_INCREMENT NOT NULL, name_romaji VARCHAR(255) DEFAULT NULL, name_japanese VARCHAR(255) DEFAULT NULL, catalognumber VARCHAR(12) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE title_model (title_id INT NOT NULL, model_id INT NOT NULL, INDEX IDX_1764C4AEA9F87BD (title_id), INDEX IDX_1764C4AE7975B7E7 (model_id), PRIMARY KEY(title_id, model_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image_model ADD CONSTRAINT FK_C0D0F6703DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image_model ADD CONSTRAINT FK_C0D0F6707975B7E7 FOREIGN KEY (model_id) REFERENCES model (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jav_file ADD CONSTRAINT FK_C32B7B813F7D58D2 FOREIGN KEY (hash_id) REFERENCES file_hash (id)');
        $this->addSql('ALTER TABLE jav_file ADD CONSTRAINT FK_C32B7B81A9F87BD FOREIGN KEY (title_id) REFERENCES title (id)');
        $this->addSql('ALTER TABLE model_alias ADD CONSTRAINT FK_DF2F4B637975B7E7 FOREIGN KEY (model_id) REFERENCES model (id)');
        $this->addSql('ALTER TABLE title_model ADD CONSTRAINT FK_1764C4AEA9F87BD FOREIGN KEY (title_id) REFERENCES title (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE title_model ADD CONSTRAINT FK_1764C4AE7975B7E7 FOREIGN KEY (model_id) REFERENCES model (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jav_file DROP FOREIGN KEY FK_C32B7B813F7D58D2');
        $this->addSql('ALTER TABLE image_model DROP FOREIGN KEY FK_C0D0F6703DA5256D');
        $this->addSql('ALTER TABLE image_model DROP FOREIGN KEY FK_C0D0F6707975B7E7');
        $this->addSql('ALTER TABLE model_alias DROP FOREIGN KEY FK_DF2F4B637975B7E7');
        $this->addSql('ALTER TABLE title_model DROP FOREIGN KEY FK_1764C4AE7975B7E7');
        $this->addSql('ALTER TABLE jav_file DROP FOREIGN KEY FK_C32B7B81A9F87BD');
        $this->addSql('ALTER TABLE title_model DROP FOREIGN KEY FK_1764C4AEA9F87BD');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_role');
        $this->addSql('DROP TABLE file_hash');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE image_model');
        $this->addSql('DROP TABLE jav_file');
        $this->addSql('DROP TABLE model');
        $this->addSql('DROP TABLE model_alias');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE title');
        $this->addSql('DROP TABLE title_model');
    }
}
