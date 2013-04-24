<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130323165244 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start DATETIME NOT NULL, created DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE talk (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, speaker_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, url LONGTEXT NOT NULL, `order` INT DEFAULT NULL, created DATETIME NOT NULL, INDEX IDX_9F24D5BB71F7E88B (event_id), INDEX IDX_9F24D5BBD04A0F27 (speaker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE talk ADD CONSTRAINT FK_9F24D5BB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)");
        $this->addSql("ALTER TABLE talk ADD CONSTRAINT FK_9F24D5BBD04A0F27 FOREIGN KEY (speaker_id) REFERENCES `user` (id)");
        $this->addSql("ALTER TABLE user ADD firstname VARCHAR(255) NOT NULL AFTER email, ADD lastname VARCHAR(255) NOT NULL AFTER firstname, ADD description LONGTEXT NOT NULL AFTER lastname, ADD url LONGTEXT NOT NULL AFTER description, ADD twitter VARCHAR(255) NOT NULL after url, ADD has_gravatar TINYINT(1) NOT NULL AFTER url");
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("ALTER TABLE talk DROP FOREIGN KEY FK_9F24D5BB71F7E88B");
        $this->addSql("ALTER TABLE talk DROP FOREIGN KEY FK_9F24D5BBD04A0F27");
        $this->addSql("DROP TABLE event");
        $this->addSql("DROP TABLE talk");
        $this->addSql("ALTER TABLE user DROP firstname, DROP lastname, DROP description, DROP url, DROP twitter, DROP has_gravatar");
    }
}