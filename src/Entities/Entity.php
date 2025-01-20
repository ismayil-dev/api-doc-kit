<?php

namespace IsmayilDev\LaravelDocKit\Entities;

use Illuminate\Support\Str;
use IsmayilDev\LaravelDocKit\Generators\ModelExampleIdGenerator;

readonly class Entity
{
    public function __construct(
        protected string $entity
    ) {}

    public function operationId(string $prefix): string
    {
        return Str::camel("$prefix {$this->name()}");
    }

    public function name(): string
    {
        return $this->getEntity();
    }

    public function getPluralName(): string
    {
        return Str::plural($this->name());
    }

    public function description(string $prefix, bool $isPlural = false): string
    {
        $title = Str::title(Str::snake($this->name(), ' '));
        $title = $isPlural ? Str::plural($title) : $title;
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
        $instance = new $this->entity;

        return (string) ModelExampleIdGenerator::model($instance)->generate();
    }

    private function getEntity(): string
    {
        if (class_exists($this->entity)) {
            return class_basename($this->entity);
        }

        return $this->entity;
    }
}
