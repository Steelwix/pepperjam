<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        #[Autowire('%env(PEPPERJAM_INVITE_CODE)%')] string $invitationCode,
    ): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $providedCode = (string) $form->get('pepperjamCode')->getData();

            if ($invitationCode === '' || !hash_equals($invitationCode, $providedCode)) {
                $form->get('pepperjamCode')->addError(new FormError('This invitation code is invalid.'));
            } else {
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                $user->setEnabled(false);
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Your account request has been created and is waiting for approval.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
