<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
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

#[OA\Tag(name: 'Category')]
class CategoryController extends AbstractController
{
    #[Route('api/v1/categories', name: 'app_category', methods: ['GET'])]
    public function getCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        
        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('api/v1/categories/{id}', name: 'app_category_show', requirements:['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getCategory(Category $category, CategoryRepository $categoryRepository): JsonResponse
    {
        if (!$category) {
            return $this->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('api/v1/categories', name: 'app_category_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function createCategory(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        $em->persist($category);
        $em->flush();

        return $this->json(['Data' => $category], Response::HTTP_CREATED);
    }

    #[Route('api/v1/categories/{id}', name: 'app_category_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function updateCategory(Request $request, Category $Category, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $category = $serializerInterface->deserialize($request->getContent(), Category::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $Category]);
        
        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        // var_dump($errors);die;
        
        $em->persist($category);
        $em->flush();

        $location = $urlGenerator->generate('app_category', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($category, Response::HTTP_OK, ['Location' => $location], ['groups' => 'category:read']);
    }

    #[Route('api/v1/categories/{id}', name: 'app_category_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    public function deleteCategory(Category $category, CategoryRepository $categoryRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($category);
        $em->flush();

        return $this->json(['message' => 'Category deleted'], Response::HTTP_NO_CONTENT);
    }
}
