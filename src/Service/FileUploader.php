<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $targetDirectory = $this->getTargetDirectory();
        if (!is_dir($targetDirectory)) {
            @mkdir($targetDirectory, 0775, true);
        }

        $extension = $file->guessExtension();
        if (!$extension) {
            $extension = $file->getClientOriginalExtension() ?: 'bin';
        }

        $fileName = $safeFilename.'-'.uniqid().'.'.$extension;

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new FileException('Erreur lors de l\'upload du fichier: ' . $e->getMessage());
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
