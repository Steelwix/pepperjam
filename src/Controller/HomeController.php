<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[AllowDynamicProperties] class HomeController extends AbstractController
    {
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {

        $editing = $request->query->getBoolean('editing');
        if ($editing && !$this->isGranted('ROLE_ADMIN')) {
            $editing = false;
        }

        return $this->render('home/index.html.twig', [
            'editing' => $editing,
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(
        Request $request,
        MailerInterface $mailer,
        #[Autowire('%contact_mail%')] string $contactMail,
    ): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = (new Email())
                ->from($data['email'])
                ->to($contactMail)
                ->replyTo($data['email'])
                ->subject('[Pepperjam contact] '.$data['subject'])
                ->text(sprintf("Name: %s\nEmail: %s\n\n%s", $data['name'], $data['email'], $data['message']));

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Thanks for reaching out. Your message has been sent.');

                return $this->redirectToRoute('app_contact');
            } catch (TransportExceptionInterface $exception) {
                $this->addFlash('danger', 'Your message could not be sent. Please try again in a moment.');
            }
        }

        return $this->render('home/contact.html.twig', ['contactForm' => $form]);
    }

    }
