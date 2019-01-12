<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Cleans up records caused by a bug creating duplicate title records with identical catalognumber
 */
final class Version20190111172250 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migrations can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM title WHERE id IN (SELECT t.id FROM title t LEFT JOIN jav_file f on t.id = f.title_id WHERE f.path IS NULL)');
        $this->addSql('ALTER TABLE title ADD UNIQUE INDEX idxcatno (catalognumber)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
