<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Models;

use Illuminate\Support\Str;

readonly class DocEntity
{
    private bool $isModel;

    public function __construct(
        protected string $entity,
        protected ?string $keyType = null,
        protected string|int|null $exampleId = null,
    ) {
        $this->isModel = class_exists($this->entity);
    }

    public function name(): string
    {
        return $this->getEntity();
    }

    public function getPluralName(): string
    {
        return Str::plural($this->name());
    }

    public function getSingularName(): string
    {
        return Str::singular($this->name());
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
        // Return custom example ID if provided
        if ($this->exampleId !== null) {
            return $this->exampleId;
        }

        // For model classes, use ModelExampleIdGenerator
        if ($this->isModel) {
            return ModelExampleIdGenerator::model($this->instance())->generate();
        }

        // For static strings, generate example ID from entity name
        return Str::lower($this->name()).'-id';
    }

    public function keyType(): string
    {
        // Return custom key type if provided
        if ($this->keyType !== null) {
            return $this->keyType;
        }

        // For model classes, get key type from model instance
        if ($this->isModel) {
            return $this->instance()->getKeyType();
        }

        // For static strings, default to 'int'
        return 'int';
    }

    /**
     * Get model instance (only for model classes)
     *
     * @throws \RuntimeException if entity is not a model class
     */
    private function instance()
    {
        if (! $this->isModel) {
            throw new \RuntimeException("Cannot instantiate static string entity '{$this->entity}'");
        }

        return new $this->entity;
    }

    private function getEntity(): string
    {
        if ($this->isModel) {
            return class_basename($this->entity);
        }

        return $this->entity;
    }

    public function getResourceName(): string
    {
        $defaultSuffix = config('api-doc-kit.responses.default_response_suffix') ?? 'Dto';

        return $this->getSingularName().$defaultSuffix;
    }
}
