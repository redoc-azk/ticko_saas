<?php

namespace App\Controller;

use App\Dto\ParticipantsDto;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(
        EntityManagerInterface $em,
        #[MapRequestPayload] ParticipantsDto $participantsDto
    ): Response
    {
        try{
            // Validation

            $em->persist($participantsDto->toEntity());
            $em->flush();
            return $this->json([
                'status' => 'success',
                'message' => 'Inscription effectuée avec succès',
            ]);
        }catch (\Exception $e){
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }



    #[Route('/app', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

    #[Route('/app/list/participants', name: 'app_list_participants')]
    public function list_participants(ParticipantsRepository $participantsRepository): Response
    {
        return $this->render('app/list_participants.html.twig', [
            'participants' => $participantsRepository->findAll(),
        ]);
    }
}
