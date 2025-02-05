<?php

namespace App\Controller;

use App\Entity\Editor;
use App\Repository\EditorRepository;
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

#[OA\Tag(name: 'Editor')]
class EditorController extends AbstractController
{
    #[Route('api/v1/editors', name: 'app_editor', methods: ['GET'])]
    public function getEditors(EditorRepository $editorRepository): JsonResponse
    {
        $editors = $editorRepository->findAll();
        
        return $this->json($editors, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('api/v1/editors/{id}', name: 'app_editor_show', requirements:['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getEditor(Editor $editor, EditorRepository $editorRepository): JsonResponse
    {
        if (!$editor) {
            return $this->json(['message' => 'Editor not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('api/v1/editors', name: 'app_editor_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function createEditor(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $editor = $serializer->deserialize($request->getContent(), Editor::class, 'json');

        $errors = $validator->validate($editor);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        $em->persist($editor);
        $em->flush();

        return $this->json(['Data' => $editor], Response::HTTP_CREATED);
    }

    #[Route('api/v1/editors/{id}', name: 'app_editor_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function updateEditor(Request $request, Editor $editor, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $editor = $serializerInterface->deserialize($request->getContent(), Editor::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $editor]);
        
        $errors = $validator->validate($editor);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        
        $em->persist($editor);
        $em->flush();

        $location = $urlGenerator->generate('app_editor', ['id' => $editor->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($editor, Response::HTTP_OK, ['Location' => $location], ['groups' => 'editor:read']);
    }

    #[Route('api/v1/editors/{id}', name: 'app_editor_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function deleteEditor(Editor $editor, EditorRepository $editorRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($editor);
        $em->flush();

        return $this->json(['message' => 'Editor deleted'], Response::HTTP_NO_CONTENT);
    }
}