<?php

namespace App\Controller;

use ACSEO\TypesenseBundle\Finder\TypesenseQuery;
use App\Entity\Participants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TPSenseController extends AbstractController
{
    private $participantFinder;

    public function __construct($participantFinder)
    {
        $this->participantFinder = $participantFinder;
    }

    public function search($searchTerm) : array
    {
        $query = new TypesenseQuery($searchTerm, 'nom_prenoms');
        return $this->participantFinder->query($query)->getResults();
    }

    #[Route('/search', name: 'app_t_p_sense')]
    public function index(Request $request): JsonResponse
    {
        $q = $request->query->get('q');
        $results = $this->search($q);
        dump($results);
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TPSenseController.php',
        ]);
    }
}
