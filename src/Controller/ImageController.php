<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    #[Route('/image', name: 'app_image')]
    public function index(): Response
    {
        $destination ='ImageUpload';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($destination)) 
        {
            $filesystem->mkdir($destination);
        }
        $finder = new Finder();
        $finder->files()->in('ImageUpload');
        $imageFiles = [];
        foreach ($finder as $file) {
            $imageFiles[] = $file->getFilename();
        }

        return $this->render('image/index.html.twig', [
            'imageFiles' => $imageFiles,
        ]);
    }
    #[Route('/image/uploadImage', name:'app_upload_Image')]
    public function imageUpload(Request $request)
    {
        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('imageFile');

            if ($uploadedFile)
             { 
                $file = new File($uploadedFile);
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

                if (!in_array($file->getMimeType(), $allowedMimeTypes))
                {
                    $this->addFlash('error', 'Seuls les fichiers image (JPEG, PNG, GIF) sont autorisés.');
                    return $this->redirectToRoute('app_image');
                }

                $destination = 'ImageUpload';
                $filename = $uploadedFile->getClientOriginalName();
                $uploadedFile->move($destination, $filename);

                return $this->redirectToRoute('app_image');
            }
        }
        return $this->render('image/index.html.twig');
    }
}
