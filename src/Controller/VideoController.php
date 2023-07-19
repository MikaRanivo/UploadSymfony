<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{
    #[Route('/video', name: 'app_video')]
    public function index(): Response
    {
        $destination = 'VideoUpload';
        $file = new Filesystem();
        if(!$file->exists($destination))
        {
            $file->mkdir($destination);
        }
        $finder = new Finder();
        $finder->files()->in('VideoUpload');
        $videoFiles = [];
        foreach($finder as $file)
        {
            $videoFiles[] = $file->getFilename();
        }

        return $this->render('video/index.html.twig', [
            'videoFiles' => $videoFiles,
        ]);
    }

    #[Route('/video/uploadVideo', name: 'app_video_upload')]
    public function UploadVideo(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $uploadedfile = $request->files->get('videoFile');
        
            if($uploadedfile)
            {
                $file = new File($uploadedfile);
                $type = ['video/mp4'];
                if(!in_array($file->getMimeType(),$type))
                {
                    $this->addFlash('error','Seulement les fichier video MP4 sont autorisé');

                    return $this->redirectToRoute('app_video');
                }
                $destination = 'VideoUpload';
                $filename = $uploadedfile->getClientOriginalName();
                $uploadedfile->move($destination,$filename);

                return $this->redirectToRoute('app_video');

            }      
        }
        return $this->render('video/index.html.twig');
    }
    #[Route('/video/delete/{filename}', name: 'app_video_delete')]
    public function deleteVideo(string $filename): Response
    {
        $destination = 'VideoUpload';
        $filePath = $destination . '/' . $filename;
        $filesystem = new Filesystem();
        if($filesystem->exists($filePath))
        {
            $filesystem->remove($filePath);
            $this->addFlash('success', 'Fichier ' . $filename . ' supprimer avec succée');
        }
        return $this->redirectToRoute('app_video');
    }
     #[Route('/video/download/{filename}', name:'app_video_download')]
     public function downloadvideo(string $filename): BinaryFileResponse
     {
         $destination = 'VideoUpload';
         $filePath = $destination . '/' . $filename;
         
         return $this->file($filePath, $filename , ResponseHeaderBag::DISPOSITION_ATTACHMENT);
     }
}
