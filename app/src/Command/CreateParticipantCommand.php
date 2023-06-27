<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'redoc:create-participant')]
class CreateParticipantCommand extends Command
{
    /*$user = (new User())
    ->setEmail('az@az.com')
    ->setPrenoms('AZ')
    ->setNom('AZ')
    ->setRoles(['ROLE_ADMIN'])
    ->setPassword($passwordHasher->hashPassword(new User(), 'az'))
    ;*/
    protected static $defaultDescription = 'Create a new participant';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('mail', 'm', InputOption::VALUE_REQUIRED, 'Mail to send to')
            ->addOption('nom', 'o', InputOption::VALUE_REQUIRED, 'Nom of the participant')
            ->addOption('prenom', 'p', InputOption::VALUE_REQUIRED, 'Prenom of the participant')
            ->addOption('password', 'r', InputOption::VALUE_REQUIRED, 'Password of the participant')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
            $output->writeln([
                '==========================================',
                'Create a new participant',
                '==========================================',
                '',
            ]);
            $nom = $input->getOption('nom');
            $prenom = $input->getOption('prenom');
            $password = $input->getOption('password');
            $email = $input->getOption('mail');
            $output->writeln([
                '==========================================',
                "Nom : {$nom}",
                "Prenom : {$prenom}",
                "Password : {$password}",
                "Email : {$email}",
                '==========================================',
                '',
            ]);
            $user = (new User())
                ->setEmail($email)
                ->setPrenoms($prenom)
                ->setNom($nom)
                ->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return Command::SUCCESS;
    }
}