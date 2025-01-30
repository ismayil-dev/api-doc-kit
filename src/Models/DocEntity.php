<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Models;

use Illuminate\Support\Str;

readonly class DocEntity
{
    public function __construct(
        protected string $entity
    ) {}

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

    public function parameterDescription(string $parameterName)
    {
        $title = Str::title(Str::snake($this->name(), ' '));

        return "{$title} {$parameterName}";
    }

    public function relationParameterDescription(string $parameterName, DocEntity $relatedEntity): string
    {
        return "The {$relatedEntity->name()} ID associated with {$this->name()}";
    }

    public function tags(array $additional = []): array
    {
        return array_merge([Str::plural(Str::headline($this->name()))], $additional);
    }

    public function exampleId(): string|int
    {
        return ModelExampleIdGenerator::model($this->instance())->generate();
    }

    public function keyType()
    {
        return $this->instance()->getKeyType();
    }

    private function instance()
    {
        return new $this->entity;
    }

    private function getEntity(): string
    {
        if (class_exists($this->entity)) {
            return class_basename($this->entity);
        }

        return $this->entity;
    }
}
