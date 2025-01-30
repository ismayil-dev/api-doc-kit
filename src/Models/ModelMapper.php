<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ModelMapper
{
    /**
     * @deprecated Use $newModels instead
     */
    public array $models = [];

    public array $newModels = [];

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
                    $instance = new $fileWithNameSpace;
                    $key = strtolower($entity->name());

                    $this->newModels[$fileWithNameSpace] = [
                        'entity' => $entity,
                        'keys' => [$key],
                        'schema' => $this->getSchema($instance),
                    ];
                }
            }
        }
    }

    protected function getSchema(Model $model): array
    {
        return Schema::getColumns($model->getTable());
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
