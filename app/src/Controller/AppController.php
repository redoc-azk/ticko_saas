<?php

namespace App\Controller;

use App\Dto\ParticipantsDto;
use App\Entity\Participants;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            ], 201, [
                // Access-Control-Allow-Origin for all origins
                'Access-Control-Allow-Origin' => '*',
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

    #[Route('/app/list/participant/{id}', name: 'app_list_participant')]
    public function list_participant(Participants $p): JsonResponse
    {
        try{
            return $this->json([
                'status' => 'success',
                'message' => 'Participant trouvé',
                'data' => $p
            ]);
        }catch (\Exception $e){
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    #[Route('/app/list/participant/confirm/{id}', name: 'app_list_participant_confirm')]
    public function confirm_participant(Participants $p, EntityManagerInterface $em): JsonResponse
    {
        try{
            $p->setScannedAt(
                // date time immutable
                new \DateTimeImmutable()
            );
            $em->flush();
            return $this->json([
                'status' => 'success',
                'message' => 'Participant confirmé',
                'data' => $p
            ]);
        }catch (\Exception $e){
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    #[Route('/app/list/participants', name: 'app_list_participants')]
    public function list_participants(Request $request, ParticipantsRepository $participantsRepository): Response
    {
        $page = $request->query->get('page', 1);
        $offset = ($page - 1) * 5;
        $participants = $participantsRepository->findBy([], [], 5, $offset);
        // is there a next page ?
        $is_next_page = count($participantsRepository->findBy([], [], 1, $offset + 5)) > 0;
        // array with actual, previous and next page if exists
        $pages = [intval($page)];
        if ($page > 1) {
            $pages[] = $page - 1;
        }
        if ($is_next_page) {
            $pages[] = $page + 1;
        }
        // order pages
        sort($pages);
        $max_page_number = $participantsRepository->count([]);
        return $this->render('app/list_participants.html.twig', [
            'page' => $page,
            'pages' => $pages,
            'participants' => $participants,
            'max_page_number' => $max_page_number / 5,
            'previous_page' => $page > 1 ? $page - 1 : 1,
            'next_page' => $page + 1,
            'is_next_page' => $is_next_page ? "" : "disabled",
            'is_previous_page' => $page > 1 ? "" : "disabled"
        ]);
    }

    #[Route('/app/scanner', name: 'app_scanner')]
    public function scanner(): Response
    {
        return $this->render('app/scanner.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

    #[Route('/app/scanner/{scanCode}', name: 'app_scanner_scan')]
    public function scanner_scan(
        string $scanCode,
        EntityManagerInterface $em,
        ParticipantsRepository $participantsRepository
    ): Response
    {
        $participant = $participantsRepository->findOneBy(['scanCode' => $scanCode]);
        if ($participant) {
            $participant->setScannedAt(
                // date time immutable
                new \DateTimeImmutable()
            );
            $em->flush();
            return $this->json([
                'status' => 'success',
                'message' => 'Participant trouvé',
                'data' => $participant
            ]);
        } else {
            return $this->json([
                'status' => 'error',
                'message' => 'Participant non trouvé',
            ]);
        }
    }
}
