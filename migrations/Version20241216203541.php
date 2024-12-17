<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216203541 extends AbstractMigration
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
        $this->addSql('CREATE TABLE article (id SERIAL NOT NULL, nom VARCHAR(100) NOT NULL, qte_stock INT NOT NULL, prix DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE client (id SERIAL NOT NULL, utilisateur_id INT DEFAULT NULL, surname VARCHAR(15) NOT NULL, telephone VARCHAR(10) NOT NULL, adresse VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455E7769B0F ON client (surname)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455450FF010 ON client (telephone)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455FB88E14F ON client (utilisateur_id)');
        $this->addSql('CREATE TABLE demande_dette (id SERIAL NOT NULL, client_id INT NOT NULL, statut BOOLEAN DEFAULT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75C54B2119EB6921 ON demande_dette (client_id)');
        $this->addSql('COMMENT ON COLUMN demande_dette.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE details (id SERIAL NOT NULL, article_id INT NOT NULL, demande_id INT NOT NULL, quantite INT NOT NULL, prix DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_72260B8A7294869C ON details (article_id)');
        $this->addSql('CREATE INDEX IDX_72260B8A80E95E18 ON details (demande_id)');
        $this->addSql('CREATE TABLE dette (id SERIAL NOT NULL, client_id INT NOT NULL, date DATE NOT NULL, montant DOUBLE PRECISION NOT NULL, montant_verser DOUBLE PRECISION NOT NULL, montant_restant DOUBLE PRECISION NOT NULL, statut BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_831BC80819EB6921 ON dette (client_id)');
        $this->addSql('COMMENT ON COLUMN dette.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE dette_article (dette_id INT NOT NULL, article_id INT NOT NULL, PRIMARY KEY(dette_id, article_id))');
        $this->addSql('CREATE INDEX IDX_C5321D58E11400A1 ON dette_article (dette_id)');
        $this->addSql('CREATE INDEX IDX_C5321D587294869C ON dette_article (article_id)');
        $this->addSql('CREATE TABLE paiement (id SERIAL NOT NULL, dette_id INT NOT NULL, date DATE NOT NULL, montant DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B1DC7A1EE11400A1 ON paiement (dette_id)');
        $this->addSql('COMMENT ON COLUMN paiement.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, login VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(50) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_LOGIN ON "user" (login)');
        $this->addSql('ALTER TABLE approvisionnement ADD CONSTRAINT FK_516C3FAA7294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE approvisionnement ADD CONSTRAINT FK_516C3FAAE11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE demande_dette ADD CONSTRAINT FK_75C54B2119EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE details ADD CONSTRAINT FK_72260B8A7294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE details ADD CONSTRAINT FK_72260B8A80E95E18 FOREIGN KEY (demande_id) REFERENCES demande_dette (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dette ADD CONSTRAINT FK_831BC80819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_C5321D58E11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_C5321D587294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EE11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE approvisionnement DROP CONSTRAINT FK_516C3FAA7294869C');
        $this->addSql('ALTER TABLE approvisionnement DROP CONSTRAINT FK_516C3FAAE11400A1');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455FB88E14F');
        $this->addSql('ALTER TABLE demande_dette DROP CONSTRAINT FK_75C54B2119EB6921');
        $this->addSql('ALTER TABLE details DROP CONSTRAINT FK_72260B8A7294869C');
        $this->addSql('ALTER TABLE details DROP CONSTRAINT FK_72260B8A80E95E18');
        $this->addSql('ALTER TABLE dette DROP CONSTRAINT FK_831BC80819EB6921');
        $this->addSql('ALTER TABLE dette_article DROP CONSTRAINT FK_C5321D58E11400A1');
        $this->addSql('ALTER TABLE dette_article DROP CONSTRAINT FK_C5321D587294869C');
        $this->addSql('ALTER TABLE paiement DROP CONSTRAINT FK_B1DC7A1EE11400A1');
        $this->addSql('DROP TABLE approvisionnement');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE demande_dette');
        $this->addSql('DROP TABLE details');
        $this->addSql('DROP TABLE dette');
        $this->addSql('DROP TABLE dette_article');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE "user"');
    }
}
