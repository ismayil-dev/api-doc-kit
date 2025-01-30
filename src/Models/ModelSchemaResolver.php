<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelSchemaResolver
{
    public function resolve(Model $model): void
    {
        $schema = Schema::getColumns($model->getTable());

        $columns = collect($schema)->map(function ($column) {
            return [
                'name' => $column['name'],
                'type' => $column['type'],
            ];
        });
    }
}
