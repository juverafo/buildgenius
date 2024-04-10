<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240410093950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_purchase DROP FOREIGN KEY FK_80EF338AA76ED395');
        $this->addSql('DROP INDEX IDX_80EF338AA76ED395 ON order_purchase');
        $this->addSql('ALTER TABLE order_purchase DROP user_id');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B4E3DC13F');
        $this->addSql('DROP INDEX IDX_6117D13B4E3DC13F ON purchase');
        $this->addSql('ALTER TABLE purchase DROP order_purchase_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_purchase ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_purchase ADD CONSTRAINT FK_80EF338AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_80EF338AA76ED395 ON order_purchase (user_id)');
        $this->addSql('ALTER TABLE purchase ADD order_purchase_id INT NOT NULL');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B4E3DC13F FOREIGN KEY (order_purchase_id) REFERENCES order_purchase (id)');
        $this->addSql('CREATE INDEX IDX_6117D13B4E3DC13F ON purchase (order_purchase_id)');
    }
}
