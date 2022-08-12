<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220228151817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import_status CHANGE valid_rows valid_rows LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', CHANGE invalid_rows invalid_rows LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import_status CHANGE valid_rows valid_rows LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE invalid_rows invalid_rows LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE tblProductData CHANGE strProductName strProductName VARCHAR(50) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, CHANGE strProductDesc strProductDesc VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, CHANGE strProductCode strProductCode VARCHAR(10) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`');
    }
}
