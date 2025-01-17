<?php

namespace IsmayilDev\LaravelDocKit\Resolvers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use IsmayilDev\LaravelDocKit\Generators\ModelExampleIdGenerator;

class EntityResolver
{
    public function __construct(
        readonly protected string|Model $entity
    ) {}

    public static function model(string $modelClass): EntityResolver
    {
        return new self($modelClass);
    }

    public function operationId(string $prefix): string
    {
        return Str::camel("$prefix {$this->name()}");
    }

    public function name(): string
    {
        return $this->getEntity();
    }

    public function description(string $prefix): string
    {
        $title = Str::title(Str::snake($this->name(), ' '));
        $prefix = Str::title(Str::snake(Str::camel($prefix), ' '));

        return "$prefix $title";
    }

    public function summary(string $summary): string
    {
        return Str::title(Str::snake(Str::camel($summary), ' '));
    }

    public function tags(array $additional = []): array
    {
        return array_merge([Str::plural(Str::headline($this->name()))], $additional);
    }

    public function exampleId(): string
    {
        return (string) ModelExampleIdGenerator::model($this->entity)->generate();
    }

    private function getEntity(): string
    {
        if (class_exists($this->entity)) {
            return class_basename($this->entity);
        }

        return $this->entity;
    }
}
