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
        if (!$this->getUser()) {
            return $this->render('home/landing.html.twig');
        }

        $users = $entityManager->getRepository(User::class)->findAll();
        $transactions = $entityManager->getRepository(Transaction::class)->findAll();

        return $this->render('home/index.html.twig', [
            'users' => $users,
            'transactions' => $transactions,
        ]);
    }
} 