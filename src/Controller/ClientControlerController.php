<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
use App\Repository\DemandeDetteRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use App\Entity\DemandeDette;
use App\Form\FiltreDemandeType;
use Symfony\Component\Validator\Constraints\IsNull;
use App\Form\ValiderType;

class ClientControlerController extends AbstractController
{
    #[Route('/client/dashboard', name: 'clientDashboard.index')]
    public function dashboardClient(DetteRepository $detteRepository,Request $request,Security $security): Response
    {
        // Récupérer l'utilisateur connecté
    $user = $security->getUser();

    // Vérifier que l'utilisateur est associé à un client
    if (!($user instanceof User) || !$user->getClient()) {
        // Gérer le cas où l'utilisateur n'est pas associé à un client
        // Par exemple, afficher un message d'erreur ou rediriger vers une autre page
        return $this->redirectToRoute('app_login');
    }
    
    // Récupérer les dettes du client
        $client = $user->getClient();//Ajout
        $demandeDettes=$client->getDemandeDettes();
        $dettes=$client->getDettes();
        
        $montant=0;
        foreach ($dettes as $dette) {
            if ($dette->isStatut() == false) {
                $montant+=$dette->getMontant();
            }
        }








        return $this->render('client/dashboardClient.html.twig', [
            'dettes' => $dettes,
            'montant'=> $montant,
            'demandeDettes'=>$demandeDettes,
            'client' => $client,
        ]);
    }

    #[Route('client/dettes/client', name: 'clientClient.dettes')]
public function listerDettesClient(DetteRepository $detteRepository, Security $security): Response
{
    // Récupérer l'utilisateur connecté
    $user = $security->getUser();

    // Vérifier que l'utilisateur est associé à un client
    if (!($user instanceof User) || !$user->getClient()) {
        // Gérer le cas où l'utilisateur n'est pas associé à un client
        // Par exemple, afficher un message d'erreur ou rediriger vers une autre page
        return $this->redirectToRoute('app_login');
    }
    
    // Récupérer les dettes du client
    $client = $user->getClient();//Ajout
    $dettes = $client->getDettes();

    // Calculer le montant total dû
    $totalDus = 0;
    foreach ($dettes as $dette) {
        $totalDus += ($dette->getMontant() - $dette->getMontantVerser());
    }

    // Rendre la vue
    return $this->render('/client/clientDette.html.twig', [
        'dettes' => $dettes,
        'client' => $client,
        'totalDus' => $totalDus,
    ]);
}

#[Route('/client/dettes/{id}/details/client', name: 'clientCLIENT.details')]
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
    
            return $this->redirectToRoute('clientCLIENT.details', ['id' => $dette->getId()]);
        }
    
        return $this->render('/client/detailsDette.html.twig', [
            'paiements' => $paiements,
            'articles' => $articles,
            'dette' => $dette,
            'client' => $client,
            'approvisionnements' => $approvisionnements,
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }
    #[Route('client/mesDemandes/client', name: 'mesDemandes.index')]
    public function listerMesDemandes(DetteRepository $detteRepository, Security $security,Request $request): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
    
        // Vérifier que l'utilisateur est associé à un client
        if (!($user instanceof User) || !$user->getClient()) {
            // Gérer le cas où l'utilisateur n'est pas associé à un client
            // Par exemple, afficher un message d'erreur ou rediriger vers une autre page
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer les dettes du client
        $client = $user->getClient();//Ajout
        $mesDemandes = $client->getDemandeDettes();
    
        // Calculer le montant total dû
        $montantsParDemande = [];
        foreach ($mesDemandes as $demande) {
            $total = 0;
            foreach ($demande->getDetails() as $detail) {
                $total += $detail->getPrix();
            }
            $montantsParDemande[$demande->getId()] = $total;
        }
        $form = $this->createForm(FiltreDemandeType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
        
            if (!is_null($data['statut'])) { // Filtrer uniquement si le statut est défini
                $statut = (bool) $data['statut'];
        
                // Filtrage en mémoire sur la Collection
                $demandeDettes = $mesDemandes->filter(function (DemandeDette $demandeDette) use ($statut) {
                    return $demandeDette->isStatut() === $statut;
                });
            }
        }
        
    
        // Rendre la vue
        return $this->render('/client/mesDette.html.twig', [
            'client' => $client,
            'mesDemandes' => $mesDemandes,
            'montantsParDemande' => $montantsParDemande,
            'form' =>$form,
        ]);
    }
}
