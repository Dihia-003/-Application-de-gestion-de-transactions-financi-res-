<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-relations',
    description: 'Tester les relations entre User et Transaction',
)]
class TestRelationsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Test des relations User ↔ Transaction');

        // 1. Récupérer tous les utilisateurs
        $users = $this->entityManager->getRepository(User::class)->findAll();
        
        if (empty($users)) {
            $io->error('Aucun utilisateur trouvé. Créez d\'abord des utilisateurs.');
            return Command::FAILURE;
        }

        $io->section('1. Utilisateurs et leurs transactions :');
        
        foreach ($users as $user) {
            $transactionCount = $user->getTransactions()->count();
            $io->text(sprintf(
                '👤 %s %s (%s) - %d transaction(s)',
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail(),
                $transactionCount
            ));

            if ($transactionCount > 0) {
                foreach ($user->getTransactions() as $transaction) {
                    $io->text(sprintf(
                        '  💰 %s: %s € (%s)',
                        $transaction->getTitle(),
                        $transaction->getAmount(),
                        $transaction->getType()
                    ));
                }
            }
            $io->newLine();
        }

        // 2. Récupérer toutes les transactions
        $transactions = $this->entityManager->getRepository(Transaction::class)->findAll();
        
        if (empty($transactions)) {
            $io->warning('Aucune transaction trouvée. Créez d\'abord des transactions.');
            return Command::SUCCESS;
        }

        $io->section('2. Transactions et leurs utilisateurs :');
        
        foreach ($transactions as $transaction) {
            $user = $transaction->getUser();
            $userName = $user ? sprintf('%s %s', $user->getFirstName(), $user->getLastName()) : 'Aucun utilisateur';
            
            $io->text(sprintf(
                '💰 %s: %s € (%s) - Utilisateur: %s',
                $transaction->getTitle(),
                $transaction->getAmount(),
                $transaction->getType(),
                $userName
            ));
        }

        // 3. Statistiques
        $io->section('3. Statistiques :');
        $io->text(sprintf('📊 Nombre total d\'utilisateurs: %d', count($users)));
        $io->text(sprintf('📊 Nombre total de transactions: %d', count($transactions)));
        
        $transactionsWithUser = array_filter($transactions, fn($t) => $t->getUser() !== null);
        $io->text(sprintf('📊 Transactions avec utilisateur: %d', count($transactionsWithUser)));

        $io->success('Test des relations terminé !');
        return Command::SUCCESS;
    }
} 