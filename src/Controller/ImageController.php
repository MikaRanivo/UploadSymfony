<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
                $Types = ['image/jpeg', 'image/png', 'image/gif'];

                if (!in_array($file->getMimeType(), $Types))
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
    #[Route('\image\delete\{filename}' , name: 'app_image_delete')]
    public function ImageDelete(string $filename): Response
    {
        $destination = 'ImageUpload';
        $filepath = $destination . '/' . $filename;
        $filesystem = new Filesystem();
        if($filesystem->exists($filepath))
        {
            $filesystem->remove($filepath);
            $this->addflash('success', 'Image' .$filename. ' supprimer avec succé');
        }else
        {
            $this->addflash('error', 'Image n\'existe pas');
        }
        return $this->redirectToRoute('app_image');
    }
    #[Route('/image/download/{filename}', name:'app_image_download')]
    public function downloadImage(string $filename): BinaryFileResponse
    {
        $destination = 'ImageUpload';
        $filePath = $destination . '/' . $filename;

        return $this->file($filePath, $filename , ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}
