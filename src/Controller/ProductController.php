<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    private ProductRepository $repo;
    private EntityManager $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(Product::class);
        $this->em = $entityManager;
    }

    /**
     * @throws ORMException
     */
    #[Route('/products', name: 'app_product_create', methods: 'POST')]
    public function create(Request $request):JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setComment($data['comment']);
        $this->em->persist($product);
        $this->em->flush();
        return $this->json($data);
    }

    #[Route('/products', name: 'app_product', methods: 'GET')]
    public function getAll(): JsonResponse
    {
        $products = $this->repo->findAll();
        $result = [];
        foreach ($products as $product)
        {
            $result[] = $product->toArray();
        }

        return $this->json($result);
    }

    #[Route('/products/{id}', name: 'app_product_one', methods: 'GET')]
    public function get(int $id): JsonResponse
    {
        $result = $this->repo->find($id);
        if (!$result) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        return $this->json($result->toArray(), Response::HTTP_OK);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route('/products/{id}', name: 'app_product_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $product = $this->repo->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $data = json_decode($request->getContent(),true);
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setComment($data['comment']);
        $this->em->flush();

        return $this->json($data);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route('/products/{id}', name: 'app_product_delete', methods: 'DELETE')]
    public function delete($id)
    {
        $product = $this->repo->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $this->em->remove($product);
        $this->em->flush();

        return $this->json("product with id ".$id." deleted", Response::HTTP_OK);
    }
}
