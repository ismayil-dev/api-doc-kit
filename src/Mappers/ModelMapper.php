<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Mappers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use IsmayilDev\ApiDocKit\Entities\DocEntity;

class ModelMapper
{
    public array $models = [];

    public function __construct()
    {
        $this->prepareModels();
    }

    protected function prepareModels(): void
    {
        $modelFolders = $this->scanForModelFolders();

        foreach ($modelFolders as $modelFolder) {
            $models = File::allFiles($modelFolder);

            foreach ($models as $model) {
                $pathName = str_replace('/', '\\', $model->getPathname());
                $fileWithNameSpace = ucfirst(str_replace('.php', '', $pathName));

                // TODO: Add support to check multiple classes and class_parents
                if (is_subclass_of($fileWithNameSpace, Model::class)) {
                    $entity = new DocEntity($fileWithNameSpace);
                    $key = strtolower($entity->name());
                    $this->models[$key] = $entity;
                }
            }
        }
    }

    protected function scanForModelFolders(): array
    {
        $modelFolders = [];
        $pathsToScan = config('api-doc-kit.paths');

        foreach ($pathsToScan as $path) {
            $directories = File::directories($path);
            foreach ($directories as $directory) {
                $baseName = basename($directory);
                if ($baseName === 'Models' || $baseName === 'Model') {
                    $modelFolders[] = $directory;
                }
            }
        }

        return $modelFolders;
    }
}
