<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220120101222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tblProductData DROP FOREIGN KEY FK_2C112486DCD6110');
        $this->addSql('CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, product_id INT UNSIGNED NOT NULL, stock INT NOT NULL, INDEX IDX_3B978F9F4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F4584665A FOREIGN KEY (product_id) REFERENCES tblProductData (intProductDataId)');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP INDEX IDX_2C112486DCD6110 ON tblProductData');
        $this->addSql('ALTER TABLE tblProductData DROP stock_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE request');
        $this->addSql('ALTER TABLE tblProductData ADD stock_id INT NOT NULL');
        $this->addSql('ALTER TABLE tblProductData ADD CONSTRAINT FK_2C112486DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('CREATE INDEX IDX_2C112486DCD6110 ON tblProductData (stock_id)');
    }
}
