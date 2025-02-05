<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        
        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => 'user:read',
            'skip_null_values' => true
        ]);
    }

    #[Route('api/v1/users/{id}', name: 'app_user_show', requirements:['id' => Requirement::DIGITS], methods: ['GET'])]
    public function showUser(User $user): JsonResponse
    {
        // Check if the current user is trying to access their own data or is an admin
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

            // Hash the password before saving
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
    public function updateUser(
        Request $request,
        User $user,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // Check if the current user is trying to update their own data or is an admin
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

            // If password is being updated, hash it
            if ($request->get('password')) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $updatedUser,
                    $updatedUser->getPassword()
                );
                $updatedUser->setPassword($hashedPassword);
            }

            // Ensure non-admin users can't grant themselves admin privileges
            if (!$this->isGranted('ROLE_ADMIN')) {
                $updatedUser->setRoles($user->getRoles()); // Restore original roles
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
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Prevent deletion of the last admin user
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