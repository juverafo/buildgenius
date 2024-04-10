<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240410134102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_purchase ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_purchase ADD CONSTRAINT FK_80EF338AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_80EF338AA76ED395 ON order_purchase (user_id)');
        $this->addSql('ALTER TABLE purchase ADD product_id INT NOT NULL, ADD orderpurchase_id INT NOT NULL');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B851EB394 FOREIGN KEY (orderpurchase_id) REFERENCES order_purchase (id)');
        $this->addSql('CREATE INDEX IDX_6117D13B4584665A ON purchase (product_id)');
        $this->addSql('CREATE INDEX IDX_6117D13B851EB394 ON purchase (orderpurchase_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_purchase DROP FOREIGN KEY FK_80EF338AA76ED395');
        $this->addSql('DROP INDEX IDX_80EF338AA76ED395 ON order_purchase');
        $this->addSql('ALTER TABLE order_purchase DROP user_id');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B4584665A');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B851EB394');
        $this->addSql('DROP INDEX IDX_6117D13B4584665A ON purchase');
        $this->addSql('DROP INDEX IDX_6117D13B851EB394 ON purchase');
        $this->addSql('ALTER TABLE purchase DROP product_id, DROP orderpurchase_id');
    }
}
