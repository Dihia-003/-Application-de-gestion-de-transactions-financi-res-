<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Transaction;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(UserRepository $userRepository, TransactionRepository $transactionRepository): Response
    {
        $totalUsers = $userRepository->count([]);
        $totalTransactions = $transactionRepository->count([]);
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        $recentTransactions = $transactionRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalTransactions' => $totalTransactions,
            'recentUsers' => $recentUsers,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/transactions', name: 'app_admin_transactions')]
    public function transactions(TransactionRepository $transactionRepository): Response
    {
        $transactions = $transactionRepository->findAll();

        return $this->render('admin/transactions.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    #[Route('/user/{id}/toggle-admin', name: 'app_admin_toggle_admin')]
    public function toggleAdmin(User $user, EntityManagerInterface $em): Response
    {
        $roles = $user->getRoles();
        
        if (in_array('ROLE_ADMIN', $roles)) {
            // Retirer le rôle admin
            $roles = array_filter($roles, fn($role) => $role !== 'ROLE_ADMIN');
            $message = 'Rôle admin retiré de ' . $user->getFirstName();
        } else {
            // Ajouter le rôle admin
            $roles[] = 'ROLE_ADMIN';
            $message = 'Rôle admin ajouté à ' . $user->getFirstName();
        }
        
        $user->setRoles($roles);
        $em->flush();
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_admin_users');
    }
} 