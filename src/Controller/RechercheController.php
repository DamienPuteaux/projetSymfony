<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Employe;
use App\Repository\InscriptionRepository;

class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'app_recherche')]
    public function index(): Response
    {
        return $this->render('recherche/index.html.twig', [
            'controller_name' => 'RechercheController',
        ]);
    }

    #[Route('/rechercheFindBy', name: 'app_recherche_findBy')]
    public function rechercheFindByAction(ManagerRegistry $doctrine): Response
    {
        $employe = $doctrine->getRepository(Employe::class)->findBy(['statut' => 0, 'nom' => 'castaing']);

        return $this->render('recherche/index.html.twig', [
            'controller_name' => 'RechercheController',
            'employe' => $employe,
        ]);
    }

    #[Route('/rechercheNomPrenom', name: 'app_recherche_nomprenom')]
    public function rechercheNomPrenom(InscriptionRepository $inscriptionRepository): Response
    {
        $inscription = $inscriptionRepository->rechInscriptionsEmploye('castaing', 'Sabine');
        return $this->render('recherche/rechercheNomPrenom.html.twig', [
            'controller_name' => 'RechercheController',
            'employe' => $inscription,
        ]);
    }
}
