<?php

namespace App\Controller;

use App\Entity\Employe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as SFType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\FormationType;
use App\Entity\Formation;
use App\Entity\Inscription;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FormationRepository;

class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(FormationRepository $formationRepository): Response
    {
        $formations = $formationRepository->findAll();

        return $this->render('formation/index.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/ajoutFormation', name: 'app_formation_ajouter')]
    public function ajoutFormation(Request $request, EntityManagerInterface $entityManager, Formation $formation = null)
    {
        if ($formation === null) {
            $formation = new Formation();
        }

        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formation);
            $entityManager->flush();

            $this->addFlash('success', 'La formation a été créée avec succès.');

            return $this->redirectToRoute('app_formation');
        }

        return $this->render('formation/ajouterformation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modifFormation/{id}', name: 'app_formation_modifier')]
    public function modifFormation($id, Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
        $formation = $doctrine->getRepository(Formation::class)->find($id);

        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formation);
            $entityManager->flush();

            $this->addFlash('success', 'La formation a été modifiée avec succès.');

            return $this->redirectToRoute('app_formation');
        }

        return $this->render('formation/ajouterformation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/affFormation', name: 'app_formation_afficher')]
    public function affFormation(ManagerRegistry $doctrine): Response
    {
        $formations = $doctrine->getManager()->getRepository(Formation::class)->findAll();

        return $this->render('formation/listeformation.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/supprimerFormation/{id}', name: 'app_formation_supprimer')]
    public function supprimerFormation($id, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $formation = $doctrine->getRepository(Formation::class)->find($id);

        $nbInscriptions = $doctrine->getRepository(Inscription::class)->count(['formation' => $formation]);

        if ($nbInscriptions === 0) {
            $entityManager->remove($formation);
            $entityManager->flush();

            $this->addFlash('success', 'La formation a été supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Impossible de supprimer la formation car des inscriptions y sont liées.');
        }

        return $this->redirectToRoute('app_formation');
    }
}
