<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'User')]
class UserController extends AbstractController
{
    #[Route('api/v1/users', name: 'app_user', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, only administrators can view user list')]
    #[OA\Response(
        response: 200,
        description: 'Return the list of users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['user:read']))
        )
    )]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        
        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => 'user:read',
            'skip_null_values' => true
        ]);
    }

    #[Route('api/v1/users/{id}', name: 'app_user_show', requirements:['id' => Requirement::DIGITS], methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a user',
        content: new Model(type: User::class, groups: ['user:read'])
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Access denied')
            ]
        )
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        description: "The id of the user",
        schema: new OA\Schema(type: "string"),
        required: true
    )]
    public function showUser(User $user): JsonResponse
    {
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => 'user:read',
            'skip_null_values' => true
        ]);
    }

    #[Route('api/v1/users', name: 'app_user_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, only administrators can create new users')]
    #[OA\Response(
        response: 201,
        description: 'User created',
        content: new Model(type: User::class, groups: ['user:read'])
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
        description: 'User data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "password", type: "string", example: "password123"),
                new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string"), example: ["ROLE_USER"])
            ]
        )
    )]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        try {
            /** @var User $user */
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            return $this->json(
                ['message' => 'User created successfully'],
                Response::HTTP_CREATED,
                [],
                ['groups' => 'user:read']
            );
        } catch (\Exception $e) {
            return $this->json(
                ['message' => 'Error creating user'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('api/v1/users/{id}', name: 'app_user_update', methods: ['PUT'])]
    #[OA\Response(
        response: 200,
        description: 'User updated',
        content: new Model(type: User::class, groups: ['user:read'])
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Access denied')
            ]
        )
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        description: "The id of the user",
        schema: new OA\Schema(type: "string"),
        required: true
    )]
    #[OA\RequestBody(
        description: 'User update data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "password", type: "string", example: "newpassword123"),
                new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string"), example: ["ROLE_USER"])
            ]
        )
    )]
    public function updateUser(
        Request $request,
        User $user,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        try {
            $updatedUser = $serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
            );

            $errors = $validator->validate($updatedUser);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            if ($request->get('password')) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $updatedUser,
                    $updatedUser->getPassword()
                );
                $updatedUser->setPassword($hashedPassword);
            }

            if (!$this->isGranted('ROLE_ADMIN')) {
                $updatedUser->setRoles($user->getRoles());
            }

            $em->persist($updatedUser);
            $em->flush();

            return $this->json(
                ['message' => 'User updated successfully'],
                Response::HTTP_OK,
                [],
                ['groups' => 'user:read']
            );
        } catch (\Exception $e) {
            return $this->json(
                ['message' => 'Error updating user'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('api/v1/users/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, only administrators can delete users')]
    #[OA\Parameter(
        name: "id",
        in: "path",
        description: "The id of the user",
        schema: new OA\Schema(type: "string"),
        required: true
    )]
    #[OA\Response(
        response: 204,
        description: 'User deleted'
    )]
    #[OA\Response(
        response: 400,
        description: 'Cannot delete last admin',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Cannot delete the last admin user')
            ]
        )
    )]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        try {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $adminCount = $em->getRepository(User::class)->count(['roles' => 'ROLE_ADMIN']);
                if ($adminCount <= 1) {
                    return $this->json(
                        ['message' => 'Cannot delete the last admin user'],
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }

            $em->remove($user);
            $em->flush();

            return $this->json(
                ['message' => 'User deleted successfully'],
                Response::HTTP_NO_CONTENT
            );
        } catch (\Exception $e) {
            return $this->json(
                ['message' => 'Error deleting user'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}