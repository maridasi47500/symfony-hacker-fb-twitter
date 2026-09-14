<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914102108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
        $this->addSql('CREATE TABLE score_to_send (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, time_signature VARCHAR(255) NOT NULL, key_signature VARCHAR(255) NOT NULL, myscore VARCHAR(255) NOT NULL, pic VARCHAR(255) NOT NULL, sender_name VARCHAR(255) NOT NULL, receiver_name VARCHAR(255) NOT NULL, receiver_email VARCHAR(255) NOT NULL, lat VARCHAR(255) NOT NULL, lon VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE quit');
        $this->addSql('DROP TABLE score_to_send');
    }
}
