<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240422122414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category CHANGE name name VARCHAR(100) NOT NULL, CHANGE type type VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE name name VARCHAR(100) NOT NULL, CHANGE reseller reseller VARCHAR(100) DEFAULT NULL, CHANGE link link VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz CHANGE type type VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category CHANGE name name VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE name name VARCHAR(255) NOT NULL, CHANGE reseller reseller VARCHAR(255) DEFAULT NULL, CHANGE link link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz CHANGE type type VARCHAR(255) NOT NULL');
    }
}
