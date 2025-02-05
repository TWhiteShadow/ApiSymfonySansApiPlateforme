<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VideoGameController extends AbstractController
{
    #[Route('api/v1/video/game', name: 'app_video_game')]
    public function index(): Response
    {
        return $this->render('video_game/index.html.twig', [
            'controller_name' => 'VideoGameController',
        ]);
    }
}
