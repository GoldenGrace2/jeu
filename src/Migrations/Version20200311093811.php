<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200311093811 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jouer CHANGE partie_id partie_id INT DEFAULT NULL, CHANGE joueur_id joueur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE partie CHANGE date_fin date_fin DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD email VARCHAR(50) NOT NULL, ADD nom VARCHAR(20) DEFAULT NULL, ADD prenom VARCHAR(25) DEFAULT NULL, ADD tel VARCHAR(25) DEFAULT NULL, ADD img VARCHAR(255) DEFAULT NULL, ADD confirmation INT NOT NULL, ADD score INT NOT NULL, ADD genre VARCHAR(5) DEFAULT NULL, CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jouer CHANGE partie_id partie_id INT DEFAULT NULL, CHANGE joueur_id joueur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE partie CHANGE date_fin date_fin DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user DROP email, DROP nom, DROP prenom, DROP tel, DROP img, DROP confirmation, DROP score, DROP genre, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
