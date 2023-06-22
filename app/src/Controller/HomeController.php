<?php

namespace App\Controller;

use App\Entity\Participants;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $user = (new User())
            ->setEmail('az@az.com')
            ->setPrenoms('AZ')
            ->setNom('AZ')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($passwordHasher->hashPassword(new User(), 'az'))
        ;
        $faker = \Faker\Factory::create('fr_FR');
        for($i = 0; $i < 800; $i++) {
            $p = (new Participants())
                ->setNomPrenoms($faker->name)
                ->setEmail($faker->email)
                ->setIndicatifTelephonique($faker->randomElement(['+225', '+226', '+227', '+228', '+229']))
                ->setNumero($faker->phoneNumber)
                ->setSexe($faker->randomElement(['Homme', 'Femme']))
                ->setProfession($faker->jobTitle)
                ->setEntreprise($faker->company)
                ->setVille($faker->city)
                ->setPays($faker->country)
            ;
            $em->persist($p);
        }
        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HomeController.php',
        ]);
    }
}
