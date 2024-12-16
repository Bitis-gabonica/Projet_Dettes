<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\Approvisionnement;
use App\Entity\User;
use App\Form\ClientType;
use App\Form\DetteFilterType;
use App\Form\DetteType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\FilterClientType;
use App\Form\searchClientByTelType;
use App\Form\PaiementType;
use App\Repository\ArticleRepository;
use App\Repository\DetteRepository;

class BoutiquierController extends AbstractController
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//PARTIE CLIENT////
{
    #[Route('/boutiquier/dashboard', name: 'dashboardBoutiquier.index')]
    public function dashboardBoutiquier(ClientRepository $clientRepository,DetteRepository $detteRepository,Request $request,ArticleRepository $articleRepository): Response
    {
        $clients=$clientRepository->findAll();
        $dettes=$detteRepository->findAll();
        $articles=$articleRepository->findAll();
        $totalArticleEnStock=0;
        $dettesNonSolde=[];
        $form = $this->createForm(searchClientByTelType::class);
        $form->handleRequest($request);

        $client=null;
        if ($form->isSubmitted() && $form->isValid()) {
            $data=$form->getData();
            $client = $clientRepository->findOneBy(['telephone' => $data['numero']]);

               
        }
    

        $totalDetteNonSolde=0;
        foreach ($dettes as $key) {
            if ($key->isStatut() == false) {
                $totalDetteNonSolde += $key->getMontant() - $key->getMontantVerser();


            }
        }
        foreach ($articles as $key) {
            if ($key->getQteStock() != 0) {
                $totalArticleEnStock ++;


            }
        }
        foreach ($dettes as $key) {
            if ($key->isStatut() == false) {
                $dettesNonSolde[]=$key;
            }
        }

        






        return $this->render('boutiquier/dashboard.html.twig', [
            'clients' => $clients, 
            'dettes' => $dettes,
            'totalDetteNonSolde' => $totalDetteNonSolde,
            'totalArticleEnStock' => $totalArticleEnStock,
            'dettesNonSolde' => $dettesNonSolde,
            'form'=> $form->createView(),
             'client'=> $client,
        ]);
    }



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
public function createDette(Request $request, EntityManagerInterface $entityManager, int $id, ArticleRepository $articleRepository): Response
{
    $client = $entityManager->getRepository(Client::class)->find($id);
    if (!$client) {
        throw $this->createNotFoundException('Client non trouvé.');
    }

    $articles = $articleRepository->findAll();
    $dette = new Dette();
    $form = $this->createForm(DetteType::class, $dette);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            $dette->setClient($client);
            $client->addDette($dette);

            // Vérifier et traiter les articles soumis
            $submittedArticles = $request->request->all('articles');
            if (!is_array($submittedArticles)) {
                throw new \UnexpectedValueException('Les données pour les articles sont invalides.');
            }

            $montant = 0;

            foreach ($submittedArticles as $articleId => $data) {
                if (isset($data['selected']) && $data['selected']) {
                    $article = $articleRepository->find($articleId);
                    if (!$article) {
                        throw new \RuntimeException("Article avec ID {$articleId} non trouvé.");
                    }

                    $approvisionnement = new Approvisionnement();
                    $approvisionnement->setArticle($article);
                    $approvisionnement->setQuantite((int) $data['quantite']);
                    $approvisionnement->setTotal($article->getPrix(), (int) $data['quantite']);
                    $approvisionnement->setDette($dette);
                    $entityManager->persist($approvisionnement);
                    $dette->addApprovisionnement($approvisionnement);

                    $montant += $approvisionnement->getTotal();
                }
            }
            $dette->setMontantVerser(0);
            $dette->setMontant($montant);
            if ($dette->getMontant() == $dette->getMontantVerser()) {
                $dette->setStatut(true);
            } else {
                $dette->setStatut(false);
            }
            $dette->calculateMontantRestant();

            $entityManager->persist($dette);
            $entityManager->flush();

            $this->addFlash('success', 'La dette a été créée avec succès.');

            // Redirection après succès
            return $this->redirectToRoute('client.index');
        } catch (\Exception $e) {
            // Log ou affichage d'erreur
            $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    return $this->render('/boutiquier/client/formDette.html.twig', [
        'form' => $form->createView(),
        'articles' => $articles,
    ]);
}


    


    #[Route('/client/dettes/{id}/details', name: 'client.details')]
    public function listerDetailsDettesClient(
        DetteRepository $detteRepository,
        Request $request,
        int $id,
        EntityManagerInterface $entityManager  ): Response {

        $dette = $entityManager->getRepository(Dette::class)->find($id);
        $client = $dette->getClient();
        $articles = $dette->getArticles();
        $paiements = $dette->getPaiements();
        $approvisionnements = $dette->getApprovisionnements();
    
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
            'approvisionnements' => $approvisionnements,
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////PARTIE DETTES///
    #[Route('/boutiquier/dettes', name: 'dettes.index')]
    public function listerDettes(DetteRepository $detteRepository, Request $request): Response
    {
        $dettes = $detteRepository->findAll(); // Récupère toutes les dettes
        $form = $this->createForm(DetteFilterType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
    
            if (!is_null($data['statut'])) { // Filtrer uniquement si le statut est défini
                $statut = (bool) $data['statut'];
    
                // Filtrage en mémoire côté PHP
                $dettes = array_filter($dettes, function (Dette $dette) use ($statut) {
                    return $dette->isStatut() === $statut;
                });
            }
        }
    
        return $this->render('boutiquier/dette/dettes.html.twig', [
            'dettes' => $dettes,
            'form' => $form->createView(),
        ]);
    }
    
    
}
