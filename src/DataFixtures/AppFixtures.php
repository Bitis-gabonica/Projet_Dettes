<?php

namespace App\DataFixtures;

use App\Entity\Approvisionnement;
use App\Entity\Article;
use App\Entity\Client;
use App\Entity\DemandeDette;
use App\Entity\Details;
use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création de l'utilisateur ADMIN
        $admin = new User();
        $admin->setLogin('admin');
        $admin->setNom('Administrator');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setActive(true);

        $manager->persist($admin);

        // Création des articles
        $articles = [];
        for ($i = 0; $i < 5; $i++) {
            $article = new Article();
            $article->setNom('Article ' . ($i + 1));
            $article->setPrix(rand(10000, 30000));
            $article->setQteStock(rand(5, 20));

            $articles[] = $article;
            $manager->persist($article);
        }

        for ($i = 0; $i < 12; $i++) {
            // Création du client
            $client = new Client();
            $client->setAdresse('Sipres ' . $i);
            $client->setSurname('Client ' . $i);
            $client->setTelephone('77777857' . $i);

            $manager->persist($client);

            // Associer un utilisateur à certains clients (1 sur 2)
            if ($i % 2 == 0) {
                $user = new User();
                $user->setLogin('user' . $i);
                $user->setNom('User ' . $i);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'password' . $i));
                $user->setActive(true);

                if ($i % 4 == 0) {
                    $user->setRoles(['ROLE_CLIENT']);
                } else {
                    $user->setRoles(['ROLE_BOUTIQUIER']);
                }

                $user->setClient($client);
                $client->setUtilisateur($user);

                $manager->persist($user);
            }

            // Création de dettes
            $dette = new Dette();
            $dette->setMontant(500);
            $dette->setDate(new \DateTimeImmutable('2024-01-01'));

            // Paiement associé
            $paiement = new Paiement();
            $paiement->setDate(new \DateTimeImmutable('2024-01-01'));
            $paiement->setMontant($i % 2 == 0 ? 500 : 100);

            $dette->setMontantVerser($paiement->getMontant());
            $dette->setMontantRestant($dette->getMontant() - $dette->getMontantVerser());
            $dette->setStatut($dette->getMontant() === $dette->getMontantVerser());
            $dette->setClient($client);
            $paiement->setDette($dette);

            $manager->persist($dette);
            $manager->persist($paiement);

            // Approvisionnement
            $approvisionnement = new Approvisionnement();
            $approvisionnement->setDette($dette);
            $approvisionnement->setArticle($articles[array_rand($articles)]);
            $approvisionnement->setQuantite(10);
            $approvisionnement->setTotal($approvisionnement->getArticle()->getPrix(), $approvisionnement->getQuantite());
            $dette->addApprovisionnement($approvisionnement);

            $manager->persist($approvisionnement);

            // Création de Demande de Dette avec statut fixe
            if ($i < 6) {
                // Les 6 premières demandes auront un statut TRUE
                $details = new Details();
                $details->setArticle($articles[array_rand($articles)]);
                $details->setQuantite(10);
                $details->setPrix($details->getArticle()->getPrix() * $details->getQuantite());

                $demande = new DemandeDette();
                $demande->setClient($client);
                $demande->setDate(new \DateTimeImmutable('2024-01-01'));
                $demande->setStatut(true);
                $demande->addDetail($details);

                $manager->persist($details);
                $manager->persist($demande);
            } else {
                // Les 6 dernières demandes auront un statut FALSE
                $details = new Details();
                $details->setArticle($articles[array_rand($articles)]);
                $details->setQuantite(10);
                $details->setPrix($details->getArticle()->getPrix() * $details->getQuantite());

                $demande = new DemandeDette();
                $demande->setClient($client);
                $demande->setDate(new \DateTimeImmutable('2024-01-01'));
                $demande->setStatut(false);
                $demande->addDetail($details);

                $manager->persist($details);
                $manager->persist($demande);
            }
        }

        $manager->flush();
    }
}
