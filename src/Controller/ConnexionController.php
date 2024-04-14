<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Form\ConnexionType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class ConnexionController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/connexion', name: 'app_connexion')]
    public function connexion(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, SessionInterface $session): Response
    {
        $form = $this->createForm(ConnexionType::class);
        $form->handleRequest($request);
        $message = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $employe = $form->getData();

            $loginForm = $employe->getLogin();
            $mdp = $employe->getMdp();
            $mdpHash = md5($mdp .'15');
            $employe = $doctrine->getRepository(Employe::class)->findOneBy(['login' => $loginForm, 'mdp' => $mdpHash]);

            if ($employe) {
                $session->set('idEmploye', $employe->getId());
                $session->set('nomEmploye', $employe->getNom());
                $session->set('prenomEmploye', $employe->getPrenom());
                $this->logger->info('Connexion réussie pour l\'employé: ' . $employe->getNom() . ' ' . $employe->getPrenom());
                
                $role = ($employe->getStatut() == 0) ? 'Employé' : 'Administrateur';
                $session->set('roleEmploye', $role);

                if ($employe->getStatut() == 0) {
                    return $this->redirectToRoute('app_formation_afficher');
                } else {
                    return $this->redirectToRoute('app_formation');
                }
            } else {
                $this->logger->warning('Tentative de connexion échouée pour le login: ' . $loginForm);
                $message = 'Login ou mot de passe incorrect.';
            }
        }

        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function deconnexion(SessionInterface $session): Response
    {
        $session->invalidate();

        return $this->redirectToRoute('app_connexion');
    }
}
