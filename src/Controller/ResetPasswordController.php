<?php

namespace App\Controller;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token unique
                $token = bin2hex(random_bytes(32));
                $expiresAt = new \DateTimeImmutable('+1 hour');

                $resetRequest = new ResetPasswordRequest();
                $resetRequest->setEmail($email);
                $resetRequest->setHashedToken(hash('sha256', $token));
                $resetRequest->setExpiresAt($expiresAt);

                $em->persist($resetRequest);
                $em->flush();

                // TODO: Envoyer un email avec le lien de reset
                // Pour l'instant, on affiche le token dans un message flash
                $this->addFlash('success', sprintf(
                    'Un email de réinitialisation a été envoyé à %s. Token: %s',
                    $email,
                    $token
                ));
            } else {
                // Ne pas révéler qu'un utilisateur n'existe pas
                $this->addFlash('success', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/reset/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        ResetPasswordRequestRepository $resetRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $resetRequest = $resetRepository->findValidRequest($token);

        if (!$resetRequest) {
            $this->addFlash('error', 'Le lien de réinitialisation est invalide ou a expiré.');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $em->getRepository(User::class)->findOneBy(['email' => $resetRequest->getEmail()]);
            
            if ($user) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
                );
                
                $resetRequest->setUsed(true);
                
                $em->flush();
                
                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
} 