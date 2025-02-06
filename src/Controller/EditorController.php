<?php

namespace App\Controller;

use App\Entity\Editor;
use App\Repository\EditorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Editor')]
class EditorController extends AbstractController
{
    #[Route('api/v1/editors', name: 'app_editor', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of editors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Editor::class, groups: ['editor:read']))
        )
    )]
    public function getEditors(EditorRepository $editorRepository): JsonResponse
    {
        $editors = $editorRepository->findAll();
        return $this->json($editors, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('api/v1/editors/{id}', name: 'app_editor_show', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns an editor',
        content: new Model(type: Editor::class, groups: ['editor:read'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Editor not found',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Editor not found')
            ]
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The id of the editor',
        schema: new OA\Schema(type: 'string'),
        required: true
    )]
    public function getEditor(Editor $editor): JsonResponse
    {
        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('api/v1/editors', name: 'app_editor_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, only administrators can create editors')]
    #[OA\Response(
        response: 201,
        description: 'Editor created',
        content: new Model(type: Editor::class, groups: ['editor:read'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation failed')
            ]
        )
    )]
    #[OA\RequestBody(
        description: 'Editor data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Editor Name')
            ]
        )
    )]
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
        return $this->json($editor, Response::HTTP_CREATED, [], ['groups' => 'editor:read']);
    }

    #[Route('api/v1/editors/{id}', name: 'app_editor_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, only administrators can update editors')]
    #[OA\Response(
        response: 200,
        description: 'Editor updated',
        content: new Model(type: Editor::class, groups: ['editor:read'])
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The id of the editor',
        schema: new OA\Schema(type: 'string'),
        required: true
    )]
    public function updateEditor(
        Request $request,
        Editor $editor,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ): JsonResponse {
        $editor = $serializer->deserialize(
            $request->getContent(),
            Editor::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $editor]
        );
        $em->persist($editor);
        $em->flush();
        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor:read']);
    }

    #[Route('api/v1/editors/{id}', name: 'app_editor_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, only administrators can delete editors')]
    #[OA\Response(
        response: 204,
        description: 'Editor deleted'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The id of the editor',
        schema: new OA\Schema(type: 'string'),
        required: true
    )]
    public function deleteEditor(Editor $editor, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($editor);
        $em->flush();
        return $this->json(['message' => 'Editor deleted'], Response::HTTP_NO_CONTENT);
    }
}
