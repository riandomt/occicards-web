<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925121653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deck (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, folder_id INT NOT NULL, name VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, cards JSON NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4FAC3637A76ED395 (user_id), INDEX IDX_4FAC3637162CB942 (folder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id)');
        $this->addSql('ALTER TABLE folder ADD CONSTRAINT FK_ECA209CD727ACA70 FOREIGN KEY (parent_id) REFERENCES folder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folder ADD CONSTRAINT FK_ECA209CDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC3637A76ED395');
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC3637162CB942');
        $this->addSql('DROP TABLE deck');
        $this->addSql('ALTER TABLE folder DROP FOREIGN KEY FK_ECA209CD727ACA70');
        $this->addSql('ALTER TABLE folder DROP FOREIGN KEY FK_ECA209CDA76ED395');
    }
}
