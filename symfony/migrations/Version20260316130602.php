<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316130602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refresh_token DROP INDEX UNIQ_C74F2195A76ED395, ADD INDEX IDX_C74F2195A76ED395 (user_id)');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY `FK_C74F2195A76ED395`');
        $this->addSql('ALTER TABLE refresh_token CHANGE revoked revoked TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74F21955F37A13B ON refresh_token (token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refresh_token DROP INDEX IDX_C74F2195A76ED395, ADD UNIQUE INDEX UNIQ_C74F2195A76ED395 (user_id)');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395');
        $this->addSql('DROP INDEX UNIQ_C74F21955F37A13B ON refresh_token');
        $this->addSql('ALTER TABLE refresh_token CHANGE revoked revoked TINYINT NOT NULL');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT `FK_C74F2195A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
