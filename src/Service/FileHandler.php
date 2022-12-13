<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;

class FileHandler
{
    private $filesDirectory;
    private $slugger;
    private $kernel;

    public function __construct($filesDirectory, SluggerInterface $slugger, KernelInterface $kernel)
    {
        $this->filesDirectory = $filesDirectory;
        $this->slugger = $slugger;
        $this->kernel = $kernel;
    }


    public function getSafeFilename(UploadedFile $file){
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        return $fileName;
    }
    public function upload(UploadedFile $file, $dir = "")
    {
        $filesystem = new Filesystem();
        //$fileName = $this->getSafeFilename($file);
        $fileName = $file->getClientOriginalName();
        $path =Path::join($this->getFilesDirectory(), $dir);
        $filePath = Path::join($dir, $fileName);
        try {
            if(!$filesystem->exists($path))$filesystem->mkdir($path);
            $file->move($path, $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
            throw $e;
        }catch (IOExceptionInterface $exception) {
            throw $exception;
        }
        return $filePath;
    }

    public function getFilesDirectory()
    {
        return $this->filesDirectory;
    }

    public function getFile($filepath){
        $path = Path::join($this->getFilesDirectory(), $filepath);
        $file = new File($path);
        return $file;
    }
    public function saveBinary($data, $filename, $dir){
        $filesystem = new Filesystem();
        $path =Path::join($this->getFilesDirectory(), $dir);
        if(!$filesystem->exists($path))$filesystem->mkdir($path);
        $filePath = Path::join($dir, $filename);
        $ifp = fopen( Path::join($this->getFilesDirectory(), $filePath), 'wb' );
        fwrite( $ifp, $data);
        fclose( $ifp ); 
        return $filePath; 
    }

    public function convertImageToBase64($path){
        $path = $this->kernel->getProjectDir().'/public/'.$path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}
