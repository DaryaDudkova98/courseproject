<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Psr\Log\LoggerInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, 
    Security $security, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
{
    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var string $plainPassword */
        $plainPassword = $form->get('plainPassword')->getData();

        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
        $user->setStatus(User::STATUS_UNVERIFIED);
        $user->setRoles([]);
        $user->setLocale('en');
        $user->setTheme('auto');
        $user->setIsVerified(false);

        $entityManager->persist($user);
        $entityManager->flush();

        $logger->info('✅ User saved with ID: ' . $user->getId());

        try {
            $logger->info('📧 Attempting to send verification email to: ' . $user->getEmail());
            
            $email = (new TemplatedEmail())
                ->from(new Address('dudkovadaryadmitrievna@gmail.com', 'support CourseProject'))
                ->to($user->getEmail())  // убрал приведение к string
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig');
            
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);
            
            $logger->info('✅ Email sent successfully!');
            $this->addFlash('success', 'Registration successful! Please check your email to confirm your account.');
            
        } catch (\Exception $e) {
            $logger->error('❌ Email sending failed: ' . $e->getMessage());
            $this->addFlash('warning', 'Registration successful, but we could not send confirmation email.');
        }

        return $this->redirectToRoute('app_login');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, 
        UserRepository $userRepository, Security $security): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            $this->addFlash('error', 'Invalid verification link.');
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_register');
        }

        if ($user->isVerified()) {
            $this->addFlash('info', 'Your email is already verified.');
            return $this->redirectToRoute('app_login');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', '🎉 Your email has been verified successfully!');
        
        try {
            $security->login($user, 'form_login', 'main');
            $this->addFlash('success', 'You are now logged in.');
            return $this->redirectToRoute('homepage');
        } catch (\Exception $e) {
            $this->addFlash('info', 'You can now log in with your credentials.');
            return $this->redirectToRoute('app_login');
        }
    }
}