<?php

namespace App\Controller;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageUploadController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/items/{id}/upload-image', name: 'upload_item_image', methods: ['POST'])]
    public function uploadImage(Request $request, Item $item): Response
    {
        $file = $request->files->get('image');

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $uploadsDirectory = $this->getParameter('uploads_directory');

        $fileName = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($uploadsDirectory, $fileName);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Unable to upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $item->setImage($fileName);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Image uploaded successfully'], Response::HTTP_OK);
    }
}
