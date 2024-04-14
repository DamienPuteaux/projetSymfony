<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Formation;
use App\Entity\Employe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class InscriptionController extends AbstractController
{
    #[Route('/inscription/{id}', name: 'app_inscrire')]
    public function inscrire($id, ManagerRegistry $doctrine, Request $request, SessionInterface $session): Response
    {
        $idEmploye = $session->get('idEmploye');

        $entityManager = $doctrine->getManager();

        $formation = $entityManager->getRepository(Formation::class)->find($id);
        $employe = $entityManager->getRepository(Employe::class)->find($idEmploye);

        if (!$formation) {
            throw $this->createNotFoundException('La formation n\'existe pas');
        }

        $inscription = new Inscription();
        $inscription->setEmploye($employe);
        $inscription->setFormation($formation);
        $inscription->setStatut('En attente');

        $entityManager->persist($inscription);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit à la formation. Votre demande est en attente de validation.');

        return $this->redirectToRoute('app_formation_afficher');
    }

    #[Route('/mes-formations', name: 'app_mes_formations')]
    public function mesFormations(SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $idEmploye = $session->get('idEmploye');

        $entityManager = $doctrine->getManager();

        $inscriptions = $entityManager->getRepository(Inscription::class)->findBy(['employe' => $idEmploye]);

        return $this->render('formation/mesformations.html.twig', [
            'inscriptions' => $inscriptions,
        ]);
    }

    #[Route('/annuler-inscription/{id}', name: 'app_annuler_inscription')]
    public function annulerInscription($id, ManagerRegistry $doctrine, SessionInterface $session): Response
    {
        $entityManager = $doctrine->getManager();

        $inscription = $entityManager->getRepository(Inscription::class)->find($id);

        if (!$inscription) {
            throw $this->createNotFoundException('L\'inscription n\'existe pas');
        }

        $idEmploye = $session->get('idEmploye');
        if ($inscription->getEmploye()->getId() !== $idEmploye) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à annuler cette inscription.');
        }

        $entityManager->remove($inscription);
        $entityManager->flush();

        $this->addFlash('success', 'Inscription annulée avec succès.');

        return $this->redirectToRoute('app_mes_formations');
    }

    #[Route('/gerer-inscriptions', name: 'app_gerer_inscriptions')]
    public function gererInscriptions(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $inscriptions = $entityManager->getRepository(Inscription::class)->findBy(['statut' => 'En attente']);

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $inscriptionId = $request->request->get('inscription_id');

            $inscription = $entityManager->getRepository(Inscription::class)->find($inscriptionId);

            if (!$inscription) {
                throw $this->createNotFoundException('L\'inscription n\'existe pas');
            }

            if ($action === 'accepter') {
                $inscription->setStatut('Acceptée');
            } elseif ($action === 'refuser') {
                $inscription->setStatut('Refusée');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_gerer_inscriptions');
        }

        return $this->render('formation/gererinscriptions.html.twig', [
            'inscriptions' => $inscriptions,
        ]);
    }
}
