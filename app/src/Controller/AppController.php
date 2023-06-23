<?php

namespace App\Controller;

use ACSEO\TypesenseBundle\Finder\TypesenseQuery;
use App\Dto\ParticipantsDto;
use App\Entity\Participants;
use App\Repository\ParticipantsRepository;
use App\Service\GetQRService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AppController extends AbstractController
{
    private $participantFinder;

    public function __construct($participantFinder)
    {
        $this->participantFinder = $participantFinder;
    }

    public function search($searchTerm, $numberOfResults = 10, $page = 1)
    {
        $query =( new TypesenseQuery($searchTerm, 'nom_prenoms'))
            ->perPage($numberOfResults)
            ->page($page)
        ;
        return $this->participantFinder->query($query)->getResults();
    }

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
        // redirect to app_list_participants
        return $this->redirectToRoute('app_list_participants');
    }

    #[Route('/app/list/participant/{id}', name: 'app_list_participant')]
    public function list_participant(
        Participants $p,
        GetQRService $getQRService
    ): JsonResponse
    {
        try{
            $scannedAt = $p->getScannedAt();
            $p->setScannedBy(null);
            $p->setScannedAt($scannedAt);
            return $this->json([
                'status' => 'success',
                'message' => 'Participant trouvé',
                'qr' => ($getQRService)($p)->getDataUri(),
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
            if ($p->getScannedBy() === null){
                $p->setScannedBy($this->getUser());
            }
            $em->persist($p);
            $em->flush();
            $scannedAt = $p->getScannedAt();
            $p->setScannedBy(null);
            $p->setScannedAt($scannedAt);
            return $this->json([
                'status' => 'success',
                'message' => 'Participant confirmé',
                'data' => $p
            ]);
        }catch (\Exception $e){
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage() .  ' ' . $e->getTraceAsString(),
            ]);
        }
    }

    #[Route('/app/list/participants', name: 'app_list_participants')]
    public function list_participants(
        Request $request,
        ParticipantsRepository $participantsRepository
    ): Response
    {
        $nbItemsPerPage = 10;
        // term from post request or empty string
        $term = $request->query->get('term', '');
        $page = $request->query->get('page', 1);
        $offset = ($page - 1) * $nbItemsPerPage;
        if(empty($term)){
            $participants = $participantsRepository->findBy([], [], $nbItemsPerPage, $offset);
            $max_page_number = count($participants);
        }else{
            $participants = $this->search($term, $nbItemsPerPage, $page);
            $max_page_number = count($this->search($term));
        }
        // is there a next page ?
        $is_next_page = count($participants) == $nbItemsPerPage;
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
        $max_page_number /= 5;
        // if $max_page_number as a decimal part, then set it to the next integer
        if (intval($max_page_number) != $max_page_number) {
            $max_page_number = intval($max_page_number) + 1;
        }
        // set Content-Type: text/vnd.turbo-stream.html
        return $this->render('app/list_participants.html.twig', [
            'total_count' => $participantsRepository->count([]),
            'total_scanned' => $participantsRepository->count([]) - $participantsRepository->count([
                'scannedAt' => null
            ]),
            'term' => $term,
            'page' => $page,
            'pages' => $pages,
            'participants' => $participants,
            'max_page_number' => $max_page_number,
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
        Security $security,
        EntityManagerInterface $em,
        ParticipantsRepository $participantsRepository
    ): Response
    {
        $participant = $participantsRepository->findOneBy(['scanCode' => $scanCode]);
        if ($participant) {
            if(!$participant->getScannedAt()){
                $participant->setScannedBy(
                    $security->getUser()
                );
            }
            $em->flush();
            return $this->json([
                'status' => 'success',
                'message' => 'Participant trouvé'
            ]);
        } else {
            return $this->json([
                'status' => 'error',
                'message' => 'Participant non trouvé',
            ], 404);
        }
    }

    #[Route('/app/list/participant/infos/{scanCode}', name: 'app_list_participant_infos')]
    public function list_participant_one(
        string $scanCode,
        ParticipantsRepository $participantsRepository
    ): Response
    {
        $participant = $participantsRepository->findOneBy(['scanCode' => $scanCode]);
        if(!$participant){
            return $this->redirectToRoute('app_list_participants');
        }
        return $this->render('app/list_participant.html.twig', [
            'participant' => $participant
        ]);
    }

    #[Route('/app/participant/excel', name: 'app_participant_excel')]
    public function excel(
        ParticipantsRepository $repo,
    ){
        $actualDate = date('d-m-Y H:i:s');
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet
            ->getProperties()
            ->setCreator('Ticko')
            ->setTitle('Export - ' . $actualDate)
            ->setDescription('
                Export des participants
            ');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle('A1:M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
        $sheet->getStyle('A1:M1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->setCellValue('A1', 'N.');
        $sheet->setCellValue('B1', 'Nom & Prénoms');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Indicatif Téléphone');
        $sheet->setCellValue('E1', 'Téléphone');
        $sheet->setCellValue('F1', 'Genre');
        $sheet->setCellValue('G1', 'Profession');
        $sheet->setCellValue('H1', 'Entreprise');
        $sheet->setCellValue('I1', 'Ville');
        $sheet->setCellValue('J1', 'Pays');
        $sheet->setCellValue('K1', 'Scanné le');
        $sheet->setCellValue('L1', 'Mail envoyé le');
        $sheet->setCellValue('M1', 'Inscription le');
        $i = 2;
        foreach(
            $repo->findAll() as $participant
        ){
            $sheet->setCellValue('A' . $i, $participant->getId());
            $sheet->setCellValue('B' . $i, $participant->getNomPrenoms());
            $sheet->setCellValue('C' . $i, $participant->getEmail());
            $sheet->setCellValue('D' . $i, $participant->getIndicatifTelephonique());
            $sheet->setCellValue('E' . $i, $participant->getNumero());
            $sheet->setCellValue('F' . $i, $participant->getSexe());
            $sheet->setCellValue('G' . $i, $participant->getProfession());
            $sheet->setCellValue('H' . $i, $participant->getEntreprise());
            $sheet->setCellValue('I' . $i, $participant->getVille());
            $sheet->setCellValue('J' . $i, $participant->getPays());
            $sheet->setCellValue('K' . $i, $participant->getScannedAt() ? $participant->getScannedAt()->format('d-m-Y H:i:s') : 'NULL');
            $sheet->setCellValue('L' . $i, $participant->getMailSendedAt() ? $participant->getMailSendedAt()->format('d-m-Y H:i:s') : 'NULL');
            $sheet->setCellValue('M' . $i, $participant->getCreatedAt() ? $participant->getCreatedAt()->format('d-m-Y H:i:s') : '');
            $i++;
        }

        $filename = "export-{$actualDate}.xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);

        return new Response(
            file_get_contents($filename),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"'
            ]
        );
    }
}
