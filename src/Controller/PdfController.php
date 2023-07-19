<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    #[Route('/pdf', name: 'app_pdf')]
    public function index(): Response
    {
        $destination ='PdfUpload';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($destination)) 
        {
            $filesystem->mkdir($destination);
        }
        $finder = new Finder();
        $finder->files()->in('PdfUpload');
        $pdfFiles = [];
        foreach ($finder as $file) {
            $pdfFiles[] = $file->getFilename();
        }

        return $this->render('pdf/index.html.twig', [
            'pdfFiles' => $pdfFiles,
        ]);
    }
    #[Route('/pdf/uploadpdf', name:'app_upload_pdf')]
    public function pdfUpload(Request $request)
    {
        if ($request->isMethod('POST')) 
        {
            $uploadedFile = $request->files->get('pdfFile');

            if ($uploadedFile)
             { 
                $file = new File($uploadedFile);
                $Types = ['application/pdf'];

                if (!in_array($file->getMimeType(), $Types))
                {
                    $this->addFlash('error', 'Seuls les fichiers PDF sont autorisés.');

                    return $this->redirectToRoute('app_pdf');
                }

                $destination = 'PdfUpload';
                $filename = $uploadedFile->getClientOriginalName();
                $uploadedFile->move($destination, $filename);

                return $this->redirectToRoute('app_pdf');
            }
        }
        return $this->render('pdf/index.html.twig');
    }
    #[Route('/pdf/delete/{filename}', name:'app_pdf_delete')]
    public function deletePdf(string $filename): Response
    {
        $destination = 'PdfUpload';
        $filePath = $destination . '/' . $filename;
        $filesystem = new Filesystem();
        if($filesystem->exists($filePath))
        {
            $filesystem->remove($filePath);
            $this->addFlash('success', 'fichier '. $filename . ' supprimer avec succé');
        }else
        {
            $this->addFlash('error', 'le fichier n\'existe pas');
        }
        return $this->redirectToRoute('app_pdf');
    }
     #[Route('/pdf/download/{filename}', name:'app_pdf_download')]
     public function downloadPdf(string $filename): BinaryFileResponse
     {
         $destination = 'PdfUpload';
         $filePath = $destination . '/' . $filename;
         
         return $this->file($filePath, $filename , ResponseHeaderBag::DISPOSITION_ATTACHMENT);
     }

}
