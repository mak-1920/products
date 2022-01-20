<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220120095632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tblProductData ADD stock_id INT NOT NULL');
        $this->addSql('ALTER TABLE tblProductData ADD CONSTRAINT FK_2C112486DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C11248662F10A58 ON tblProductData (strProductCode)');
        $this->addSql('CREATE INDEX IDX_2C112486DCD6110 ON tblProductData (stock_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tblProductData DROP FOREIGN KEY FK_2C112486DCD6110');
        $this->addSql('DROP INDEX UNIQ_2C11248662F10A58 ON tblProductData');
        $this->addSql('DROP INDEX IDX_2C112486DCD6110 ON tblProductData');
        $this->addSql('ALTER TABLE tblProductData DROP stock_id');
    }
}
