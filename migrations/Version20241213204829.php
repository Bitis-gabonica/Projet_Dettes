<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213204829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE approvisionnement (id SERIAL NOT NULL, article_id INT NOT NULL, dette_id INT NOT NULL, quantite INT NOT NULL, total DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_516C3FAA7294869C ON approvisionnement (article_id)');
        $this->addSql('CREATE INDEX IDX_516C3FAAE11400A1 ON approvisionnement (dette_id)');
        $this->addSql('ALTER TABLE approvisionnement ADD CONSTRAINT FK_516C3FAA7294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE approvisionnement ADD CONSTRAINT FK_516C3FAAE11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE approvisionnement DROP CONSTRAINT FK_516C3FAA7294869C');
        $this->addSql('ALTER TABLE approvisionnement DROP CONSTRAINT FK_516C3FAAE11400A1');
        $this->addSql('DROP TABLE approvisionnement');
    }
}
