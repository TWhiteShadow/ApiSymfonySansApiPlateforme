<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\VideoGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'VideoGame')]
class VideoGameController extends AbstractController
{
    #[Route('api/v1/video-games', name: 'app_video_game', methods: ['GET'])]
    public function getVideoGames(VideoGameRepository $videoGameRepository): JsonResponse
    {
        $videoGames = $videoGameRepository->findAll();
        
        return $this->json($videoGames, Response::HTTP_OK, [], ['groups' => 'video_game:read']);
    }

    #[Route('api/v1/video-games/{id}', name: 'app_video_game_show', requirements:['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getVideoGame(VideoGame $videoGame, VideoGameRepository $videoGameRepository): JsonResponse
    {
        if (!$videoGame) {
            return $this->json(['message' => 'Video game not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($videoGame, Response::HTTP_OK, [], ['groups' => 'video_game:read']);
    }

    #[Route('api/v1/video-games', name: 'app_video_game_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function createVideoGame(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $videoGame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json');
        // dd($videoGame);
        $errors = $validator->validate($videoGame);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        $em->persist($videoGame);
        $em->flush();

        return $this->json(['Data' => $videoGame], Response::HTTP_CREATED);
    }

    #[Route('api/v1/video-games/{id}', name: 'app_video_game_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function updateVideoGame(Request $request, VideoGame $videoGame, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $videoGame = $serializerInterface->deserialize($request->getContent(), VideoGame::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $videoGame]);
        
        $errors = $validator->validate($videoGame);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        
        $em->persist($videoGame);
        $em->flush();

        $location = $urlGenerator->generate('app_video_game', ['id' => $videoGame->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($videoGame, Response::HTTP_OK, ['Location' => $location], ['groups' => 'video_game:read']);
    }

    #[Route('api/v1/video-games/{id}', name: 'app_video_game_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function deleteVideoGame(VideoGame $videoGame, VideoGameRepository $videoGameRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($videoGame);
        $em->flush();

        return $this->json(['message' => 'Video game deleted'], Response::HTTP_NO_CONTENT);
    }
}