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
use App\Form\UserType;
use App\Form\User2Type;
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
use App\Repository\DemandeDetteRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use App\Entity\DemandeDette;
use App\Form\FiltreDemandeType;
use Symfony\Component\Validator\Constraints\IsNull;
use App\Form\ValiderType;
use App\Repository\UserRepository;
use App\Form\UserFilterType;
use App\Form\ArticleFilterType;
use App\Form\ArticleType;
use App\Entity\Article;
use App\Form\ArticleQteType;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'dashboardUser.index')]
    public function indexDahboard(Request $request,ClientRepository $clientRepository,UserRepository $userRepository,ArticleRepository $articleRepository,DetteRepository $detteRepository): Response
    {
        $form=$this->createForm(searchClientByTelType::class);
        $form->handleRequest($request);
        $client=null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data=$form->getData();
            $client=$clientRepository->findOneBy(['telephone' => $data['numero']]);
        }


        $users=$userRepository->findAll();
        $articlesEnStock = $articleRepository->findAllWithStockGreaterThanZero();
        $articles=$articleRepository->findAll();
        $dettes=$detteRepository->findAll();
        $clients=$clientRepository->findAll();



        






        return $this->render('admin/dashboardUser.html.twig', [
            'form'=> $form->createView(),
            'client'=> $client,
            'users'=>$users,
            'articlesEnStock' => $articlesEnStock,
            'articles' => $articles,
            'dettes' => $dettes,
            'clients' => $clients,
        ]);
    }



    #[Route('/admin/liste-clients', name: 'clientAdmin.index')]
    public function index(ClientRepository $clientRepository, Request $request): Response
    {
        

        $form = $this->createForm(FilterClientType::class);
        $form->handleRequest($request);
        $clients=$clientRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $data=$form->getData();
            $clients=$clientRepository->filterClients($data['numero'],$data['createUser']);
               
        }

        return $this->render('admin/listeClient.html.twig', [
            'clients' => $clients,
            'form'=> $form->createView(),
        ]);
    }

    #[Route('/client/{id}/dettes/admin', name: 'clientAdmin.dettesAdmin')]

public function manageClientAndUser(
    Request $request,
    int $id,
    EntityManagerInterface $entityManager
): Response {
    // Récupération du client
    $client = $entityManager->getRepository(Client::class)->find($id);

    if (!$client) {
        throw $this->createNotFoundException("Client non trouvé");
    }

    // Récupération des dettes du client
    $dettes = $client->getDettes();


    // Gestion du formulaire de création d'utilisateur
    $user = new User();
    $userForm = $this->createForm(UserType::class, $user);
    $userForm->handleRequest($request);

    if ($userForm->isSubmitted() && $userForm->isValid()) {
        $client->setUtilisateur($user);
        $user->setClient($client);
        $user->setActive(true);
        $user->setRoles(['ROLE_CLIENT']);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur créé avec succès');
        return $this->redirectToRoute('clientAdmin.dettesAdmin', ['id' => $id]);
    }

    return $this->render('admin/clientInfo.html.twig', [
        'dettes' => $dettes,
        'client' => $client,
        'userForm' => $userForm->createView(),
    ]);
}

#[Route('/admin/liste-utilisateurs', name: 'utilisateurs.index')]
public function listUtilisateurs(
    Request $request,
    UserRepository $userRepository,
    EntityManagerInterface $entityManager,
    
): Response {
    // Récupérer tous les utilisateurs
    $users = $userRepository->findAll();

    // Vérifiez si une action est envoyée via une requête
    $action = $request->query->get('action');
    $userId = $request->query->get('id');

    if ($action && $userId) {
        $user = $userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('utilisateurs.index');
        }

        if ($action === 'activate') {
            $user->setActive(true);
            $this->addFlash('success', "Utilisateur activé avec succès");
        } elseif ($action === 'deactivate') {
            $user->setActive(false);
            $this->addFlash('success', "Utilisateur désactivé avec succès");
        }

        $entityManager->flush();

        return $this->redirectToRoute('utilisateurs.index');
    }

    $user = new User();
    $form=$this->createForm(User2Type::class,$user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setActive(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur créé avec succès');
        return $this->redirectToRoute('utilisateurs.index');
    }

   // Récupération du paramètre de filtre (activité)
   $isActive = $request->query->get('isActive');

   // Gestion du filtre
   if ($isActive !== null && $isActive !== '') {
       // Forcer la conversion en booléen
       $users = $userRepository->findBy(['isActive' => filter_var($isActive, FILTER_VALIDATE_BOOLEAN)]);
   } else {
       $users = $userRepository->findAll();
   }
    

    // Affichez la liste des utilisateurs
    return $this->render('admin/listeUser.html.twig', [
        'users' => $users,
        'form'=> $form->createView(),
        
    ]);
}


#[Route('/admin/liste-articles', name: 'article.index')]
public function listArticles(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    // Créer le formulaire de filtre
    $form = $this->createForm(ArticleFilterType::class);
    $form->handleRequest($request);

    // Récupérer tous les articles
    $articles = $articleRepository->findAll();
    $statut = $request->query->get('statut'); // Vérifie la valeur depuis l'URL

if ($statut !== null) {
    if ($statut == 1) {
        $articles = $articleRepository->createQueryBuilder('a')
            ->where('a.qteStock > 0')
            ->getQuery()
            ->getResult();
    } elseif ($statut == 0) {
        $articles = $articleRepository->createQueryBuilder('a')
            ->where('a.qteStock = 0')
            ->getQuery()
            ->getResult();
    }
}

    

    $article=new Article();
    $form2 = $this->createForm(ArticleType::class, $article);
    $form2->handleRequest($request);
    if ($form2->isSubmitted() && $form2->isValid()) {

        $entityManager->persist($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article créé avec succès');
        return $this->redirectToRoute('article.index');

    }


    $form3 = $this->createForm(ArticleQteType::class);
    $form3->handleRequest($request);
    
    if ($form3->isSubmitted() && $form3->isValid()) {
        $articleId = $request->request->get('article_id'); // Récupère l'ID de l'article depuis le formulaire
    
        if (!$articleId) {
            $this->addFlash('error', 'Identifiant de l\'article manquant.');
            return $this->redirectToRoute('article.index');
        }
    
        $article = $articleRepository->find($articleId);
    
        if (!$article) {
            $this->addFlash('error', 'Article introuvable.');
            return $this->redirectToRoute('article.index');
        }
    
        $data = $form3->getData();
        $article->setQteStock($data['quantite']);
    
        $entityManager->persist($article);
        $entityManager->flush();
    
        $this->addFlash('success', 'Stock mis à jour avec succès.');
        return $this->redirectToRoute('article.index');
    }
    

    




    return $this->render('admin/listeArticle.html.twig', [
        'articles' => $articles,
        'form' => $form->createView(),
        'form2' => $form2->createView(),
        'form3'=> $form3->createView(),
    ]);
}

#[Route('/admin/dettes', name: 'dettesAdmin.index')]
public function listerDettes(DetteRepository $detteRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupérer uniquement les dettes non archivées
    $dettes = $detteRepository->createQueryBuilder('d')
        ->where('d.statut = true') // Supposant que "statut = true" signifie non archivé
        ->getQuery()
        ->getResult();

    $form = $this->createForm(DetteFilterType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();

        if (!is_null($data['statut'])) { // Filtrer uniquement si le statut est défini
            $statut = (bool) $data['statut'];

            // Filtrer les dettes non archivées
            $dettes = $detteRepository->createQueryBuilder('d')
                ->where('d.statut = :statut')
                ->setParameter('statut', $statut)
                ->getQuery()
                ->getResult();
        }
    }

    // Traitement pour archiver une dette
    if ($request->isMethod('POST') && $request->request->has('dette_id')) {
        $detteId = $request->request->get('dette_id');

        if (!$detteId) {
            $this->addFlash('error', 'Identifiant de la dette manquant.');
            return $this->redirectToRoute('dettesAdmin.index');
        }

        $dette = $detteRepository->find($detteId);

        if (!$dette) {
            $this->addFlash('error', 'Dette introuvable.');
            return $this->redirectToRoute('dettesAdmin.index');
        }

        $dette->setStatut(false); // Marquer la dette comme archivée
        $entityManager->persist($dette);
        $entityManager->flush();

        $this->addFlash('success', 'Dette archivée avec succès.');
        return $this->redirectToRoute('dettesAdmin.index');
    }

    return $this->render('admin/listeDette.html.twig', [
        'dettes' => $dettes,
        'form' => $form->createView(),
    ]);
}








   
}
