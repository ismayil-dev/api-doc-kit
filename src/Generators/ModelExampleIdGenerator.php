<?php

namespace IsmayilDev\LaravelDocKit\Generators;

use Illuminate\Database\Eloquent\Model;

class ModelExampleIdGenerator
{
    public function __construct(
        private readonly Model $model
    ) {}

    public static function model(Model $model)
    {
        return new self($model);
    }

    public function generate()
    {
        if ($this->model->getIncrementing()) {
            return 5;
        }

        return match ($this->model->getKeyType()) {
            'string' => $this->getForString(),
            'int' => $this->model->getKey(),
            default => 5,
        };
    }

    private function getForString(): string
    {
        if (method_exists($this->model, 'newUniqueId')) {
            return $this->model->newUniqueId() ?? $this->fallback();
        }
        if (property_exists($this->model, 'keyType')
            && $this->model->getKeyType() === 'uuid'
        ) {
            return 'e6b0d245-fcd9-4d58-9e2e-dcd35d7f1b65';
        }

        return $this->fallback();
    }

    private function fallback(): string
    {
        return 'example-id';
    }
}
