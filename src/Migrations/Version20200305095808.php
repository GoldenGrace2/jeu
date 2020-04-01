<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200305095808 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE carte (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(50) NOT NULL, image VARCHAR(50) NOT NULL, type_de_carte VARCHAR(1) NOT NULL, effet VARCHAR(255) NOT NULL, cout DOUBLE PRECISION NOT NULL, prix_vente DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cases (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(30) NOT NULL, position INT NOT NULL, effet VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jouer (id INT AUTO_INCREMENT NOT NULL, partie_id INT DEFAULT NULL, joueur_id INT DEFAULT NULL, argent DOUBLE PRECISION NOT NULL, classement INT NOT NULL, cartes LONGTEXT DEFAULT NULL, pion VARCHAR(20) NOT NULL, position INT NOT NULL, tour INT NOT NULL, INDEX IDX_825E5AEDE075F7A4 (partie_id), INDEX IDX_825E5AEDA9E2D76C (joueur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partie (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, qui_joue INT NOT NULL, etat_partie VARCHAR(10) NOT NULL, gagnant INT NOT NULL, pioche LONGTEXT NOT NULL, defausse LONGTEXT DEFAULT NULL, cagnotte DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE jouer ADD CONSTRAINT FK_825E5AEDE075F7A4 FOREIGN KEY (partie_id) REFERENCES partie (id)');
        $this->addSql('ALTER TABLE jouer ADD CONSTRAINT FK_825E5AEDA9E2D76C FOREIGN KEY (joueur_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jouer DROP FOREIGN KEY FK_825E5AEDE075F7A4');
        $this->addSql('ALTER TABLE jouer DROP FOREIGN KEY FK_825E5AEDA9E2D76C');
        $this->addSql('DROP TABLE carte');
        $this->addSql('DROP TABLE cases');
        $this->addSql('DROP TABLE jouer');
        $this->addSql('DROP TABLE partie');
        $this->addSql('DROP TABLE user');
    }
}
