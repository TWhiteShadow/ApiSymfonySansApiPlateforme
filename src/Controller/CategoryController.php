<?php

namespace App\Controller;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{
    #[Route('api/v1/categories', name: 'app_category', methods: ['GET'])]
    public function getCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();

        // var_dump($categories);die;
        
        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('api/v1/categories/{id}', name: 'app_category_show', methods: ['GET'])]
    public function getCategory(Category $category, CategoryRepository $categoryRepository): JsonResponse
    {
        if (!$category) {
            return $this->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('api/v1/categories', name: 'app_category_create', methods: ['POST'])]
    public function createCategory(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {

        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');
        $em->persist($category);
        $em->flush();

        $location = $urlGenerator->generate(    
            'app_category_show',
            ['id' => $category->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['message' => 'Category created', 'location' => $location], Response::HTTP_CREATED);
    }


    #[Route('api/v1/categories/{id}', name: 'app_category_update', methods: ['PUT'])]
    public function updateCategory(Category $category, CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->json(['message' => 'Category updated'], Response::HTTP_OK);
    }

    #[Route('api/v1/categories/{id}', name: 'app_category_delete', methods: ['DELETE'])]
    public function deleteCategory(Category $category, CategoryRepository $categoryRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($category);
        $em->flush();

        return $this->json(['message' => 'Category deleted'], Response::HTTP_NO_CONTENT);
    }
}
