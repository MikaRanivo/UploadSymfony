<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class RandomFichierController extends AbstractController
{
    #[Route('/random', name: 'app_random')]
    public function index(): Response
    {   
        $destination ='RandomUpload';
        $filesystem = new Filesystem();

        if (!$filesystem->exists($destination)) 
        {
            $filesystem->mkdir($destination);
        }

        $randomFiles = [];
        $finder = new Finder();
        $finder->files()->in('RandomUpload');

        foreach($finder as $file)
        {
            $randomFiles[] = $file->getFilename();
        }
        return $this->render('random_fichier/index.html.twig', [
                'randomFiles' => $randomFiles,
        ]);
    }

    #[Route('/random/randomUpload' , name : "app_upload_random")]
    public function randomUpload(Request $request)
    {
        if($request->isMethod('POST'))
        {
            $uploadedFile = $request->files->get('randomFile');

            if ($uploadedFile)
            {
                $destination = 'RandomUpload';
                $filename = $uploadedFile->getClientOriginalName();
                $uploadedFile->move($destination, $filename);

                return $this->redirectToRoute('app_random');
            }
        }
        return $this->render('random_fichier/index.html.twig');
    }

    #[Route('/random/delete/{filename}', name : 'app_random_delete')]
    public function deleteRandom(string $filename): Response
    {
        $destination= 'RandomUpload';
        $filePath = $destination .'/'. $filename;
        $filesystem = new Filesystem();
        if($filesystem->exists($filePath))
        {
            $filesystem->remove($filePath);
            $this->addFlash('success', ' le fichier '. $filename. ' est supprimer avec succée');
        }
        return $this->redirectToRoute('app_random');
    }

     #[Route('/random/download/{filename}', name:'app_random_download')]
     public function downloadRandom(string $filename): BinaryFileResponse
     {
         $destination = 'RandomUpload';
         $filePath = $destination . '/' . $filename;
         
         return $this->file($filePath, $filename , ResponseHeaderBag::DISPOSITION_ATTACHMENT);
     }
}
