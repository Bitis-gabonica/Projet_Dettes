<?php

namespace App\DataFixtures;

use App\Entity\Approvisionnement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Client;
use App\Entity\User;
use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\Article;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher) 
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 12; $i++) {
            // Création du client
            $client = new Client();
            $client->setAdresse('sipres' . $i);
            $client->setSurname('surname' . $i);
            $client->setTelephone('77777857' . $i);

            // Création de l'utilisateur (parfois associé au client)
            if ($i % 2 == 0) {
                $user = new User();
                $user->setLogin('login' . $i);
                $user->setNom('nom' . $i);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'password' . $i));

                // Définir les rôles et l'état actif
             if ($i == 8) {
                $user->setRoles(['ROLE_CLIENT']);
             }else{
                $user->setRoles(['ROLE_BOUTIQUIER']);
                
             }
               


                // Associer le user au client
                $user->setActive(true);
                $user->setClient($client);
                $client->setUtilisateur($user);

                $manager->persist($user);
            }

            $manager->persist($client);

            // Création d'une dette
            $dette = new Dette();
            $dette->setMontant(500);
            $dette->setDate(new \DateTimeImmutable(('2024-01-01')));
            // Création du paiement associé
            $paiement = new Paiement();
            $paiement->setDate(new \DateTimeImmutable(('2024-01-01')));
            if ($i % 2 == 0) {
                $paiement->setMontant(500);
                $dette->setMontantVerser(500);
            } else {
                $paiement->setMontant(100);
                $dette->setMontantVerser(100);
            }

            $dette->setMontantRestant($dette->getMontant() - $dette->getMontantVerser());
            $dette->setClient($client);
            $paiement->setDette($dette);
            if ($dette->getMontant() == $dette->getMontantVerser()) {
                $dette->setStatut(true);
            } else {
                $dette->setStatut(false);
            }

            $manager->persist($dette);
            $manager->persist($paiement);

            // Création de l'article
            $article = new Article();
            $article->setNom('article' . $i);
            $article->setPrix(20000);
            $article->setQteStock(10);
            $dette->addArticle($article);
            // Creation de l'approvisionnement

            $approvisionnement=new Approvisionnement();
            $approvisionnement->setDette($dette);
            $approvisionnement->setArticle($article);
            $approvisionnement->setQuantite(10);
            $approvisionnement->setTotal($article->getPrix(),$approvisionnement->getQuantite());
            $dette->addApprovisionnement($approvisionnement);


            $manager->persist($dette);
            $manager->persist($paiement);
            $manager->persist($article);
            $manager->persist($approvisionnement);
        }

        // Sauvegarde dans la base
        $manager->flush();
    }
}
