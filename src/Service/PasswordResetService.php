<?php

namespace App\Service;

use App\Entity\EmailQueue;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private MailingService $mailingService,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Generate 6-digit code and send reset email
     */
    public function generateAndSendResetCode(string $email): bool
    {
        $email = mb_strtolower(trim($email));

        $user = $this->userRepository->findOneBy(['email' => $email]);
        
        if (!$user) {
            return false; // User not found
        }

        // Generate 6-digit code
        $resetCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set expiration to 15 minutes from now
        $expiresAt = new \DateTime('+15 minutes');
        
        $user->setResetToken($resetCode)
            ->setResetTokenExpiresAt($expiresAt);
        
        $this->entityManager->flush();

        // Send immediately for password reset (critical flow)
        $fullName = trim(($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? ''));

        $sent = $this->mailingService->sendEmailFromTemplateNow(
            $user->getEmail(),
            'password_reset',
            [
                'code' => $resetCode,
                'name' => $user->getFirstName() ?: $user->getEmail(),
                'expiresIn' => '15 minutes'
            ],
            $fullName !== '' ? $fullName : null
        );

        if ($sent) {
            $this->entityManager->createQueryBuilder()
                ->delete(EmailQueue::class, 'q')
                ->where('q.recipientEmail = :email')
                ->andWhere('q.subject LIKE :subjectPrefix')
                ->setParameter('email', $user->getEmail())
                ->setParameter('subjectPrefix', 'Reset Your Password - Code:%')
                ->getQuery()
                ->execute();
        }

        return $sent;
    }

    /**
     * Verify reset code and return user if valid
     */
    public function verifyCode(string $email, string $code): ?User
    {
        $user = $this->userRepository->findOneBy([
            'email' => $email,
            'resetToken' => $code
        ]);

        if (!$user) {
            return null;
        }

        // Check if token is expired
        if ($user->getResetTokenExpiresAt() < new \DateTime()) {
            return null;
        }

        return $user;
    }

    /**
     * Reset password after verifying code
     */
    public function resetPassword(string $email, string $code, string $newPassword): bool
    {
        $user = $this->verifyCode($email, $code);
        
        if (!$user) {
            return false;
        }

        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        
        // Clear reset token
        $user->setResetToken(null)
            ->setResetTokenExpiresAt(null);
        
        $this->entityManager->flush();

        return true;
    }

    /**
     * Clear expired tokens (cleanup task)
     */
    public function clearExpiredTokens(): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(User::class, 'u')
            ->set('u.resetToken', ':null')
            ->set('u.resetTokenExpiresAt', ':null')
            ->where('u.resetTokenExpiresAt < :now')
            ->setParameter('null', null)
            ->setParameter('now', new \DateTime());

        return $qb->getQuery()->execute();
    }
}
