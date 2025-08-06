<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $transactions = $entityManager->getRepository(Transaction::class)->findAll();

        // Si pas d'utilisateurs, rediriger vers le login
        if (empty($users)) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/index.html.twig', [
            'users' => $users,
            'transactions' => $transactions,
        ]);
    }
} 