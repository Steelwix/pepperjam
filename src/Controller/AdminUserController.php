<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users', name: 'app_admin_users')]
class AdminUserController extends AbstractController
{
    #[Route('', name: '_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $pendingUsers = $entityManager->getRepository(Users::class)->findBy(
            ['enabled' => false],
            ['id' => 'ASC'],
        );

        return $this->render('admin/users.html.twig', ['pendingUsers' => $pendingUsers]);
    }

    #[Route('/{id}/enable', name: '_enable', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function enable(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('enable-user-'.$id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user instanceof Users) {
            throw $this->createNotFoundException('User not found.');
        }

        $user->setEnabled(true);
        $entityManager->flush();
        $this->addFlash('success', sprintf('Account for %s has been activated.', $user->getUsername()));

        return $this->redirectToRoute('app_admin_users_index');
    }
}
