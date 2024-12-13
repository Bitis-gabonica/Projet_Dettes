<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\User;
use App\Form\ClientType;
use App\Form\DetteType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\FilterClientType;
use App\Form\PaiementType;
use App\Repository\DetteRepository;

class BoutiquierController extends AbstractController
{
    #[Route('/boutiquier/liste-clients', name: 'client.index')]
    public function index(ClientRepository $clientRepository, Request $request): Response
    {
        

        $form = $this->createForm(FilterClientType::class);
        $form->handleRequest($request);
        $clients=$clientRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $data=$form->getData();
            $clients=$clientRepository->filterClients($data['numero'],$data['createUser']);
               
        }

        return $this->render('boutiquier/index.html.twig', [
            'clients' => $clients,
            'form'=> $form->createView(),
        ]);
    }

    #[Route('/client/form', name: 'client.create')]
    public function create(\Symfony\Component\HttpFoundation\Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('createUser')->getData()) {
                $userData = $form->get('newUser')->getData();
               // $roles = array_merge(is_array($userData->getRoles()) ? $userData->getRoles(): [$userData->getRoles()]);
                
                $user = new User();
                $user->setRoles(['ROLE_CLIENT']);
                $user->setLogin($userData->getLogin());
                $user->setNom($userData->getNom());
                $user->setActive(true);
                
                $user->setPassword($userPasswordHasher->hashPassword($user,$userData->getPassword()));
    
                $entityManager->persist($user);
                $client->setUtilisateur($user);
            }
    
            $entityManager->persist($client);
            $entityManager->flush();
    
            return $this->redirectToRoute('client.index');
        }
    
        return $this->render('/boutiquier/client/form.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/client/{id}/dettes', name: 'client.dettes')]
    public function listerDettesClient(DetteRepository $detteRepository,Request $request,int $id,ClientRepository $clientRepository,EntityManagerInterface $entityManager): Response
    {

        $client = $entityManager->getRepository(Client::class)->find($id);
        $dettes=$client->getDettes();
        $totalDus=0;
        foreach ($dettes as $dette) {
            $totalDus=+($dette->getMontant()-$dette->getMontantVerser())+$totalDus;
        }


        

        return $this->render('boutiquier/client/detteClient.html.twig', [
            'dettes' => $dettes,
            'client' => $client,
            'totalDus' => $totalDus,
            
        ]);
    }
    #[Route('/dette/{id}/form', name: 'dette.create')]
    public function createDette(Request $request, EntityManagerInterface $entityManager,int $id): Response
    {
        $dette = new Dette();
        $form = $this->createForm(DetteType::class, $dette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $entityManager->getRepository(Client::class)->find($id);
            // Ajout du client à la dette

                $dette->setClient($client);
                $client->addDette($dette);
            // Calcul du montant restant
            $dette->calculateMontantRestant();
            // Ajout des articles
            foreach ($dette->getArticles() as $article) {
                $dette->addArticle($article);
            }

            // Ajout des paiements
            foreach ($dette->getPaiements() as $paiement) {
                $paiement->setDette($dette);
            }

            $entityManager->persist($dette);
            $entityManager->flush();

            $this->addFlash('success', 'La dette a été créée avec succès.');
            return $this->redirectToRoute('client.index');
        }

        return $this->render('/boutiquier/client/formDette.html.twig', [
            'form' => $form->createView(),
            'dette' => $dette,
        ]);
    }
    #[Route('/client/dettes/{id}/details', name: 'client.details')]
    public function listerDetailsDettesClient(
        DetteRepository $detteRepository,
        Request $request,
        int $id,
        EntityManagerInterface $entityManager
    ): Response {
        $dette = $entityManager->getRepository(Dette::class)->find($id);
        $client = $dette->getClient();
        $articles = $dette->getArticles();
        $paiements = $dette->getPaiements();
    
        // Création du formulaire de paiement
        $paiement = new Paiement();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $dette->addPaiement($paiement);
            $paiement->setDette($dette);
            $dette->setMontantVerser($dette->getMontantVerser() + $paiement->getMontant());
            $dette->calculateMontantRestant();
            $entityManager->persist($paiement);
            $entityManager->flush();
    
            $this->addFlash('success', 'Paiement ajouté avec succès.');
    
            return $this->redirectToRoute('client.details', ['id' => $dette->getId()]);
        }
    
        return $this->render('boutiquier/client/detailsClient.html.twig', [
            'paiements' => $paiements,
            'articles' => $articles,
            'dette' => $dette,
            'client' => $client,
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }
    


}
