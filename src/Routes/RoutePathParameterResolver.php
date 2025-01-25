<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Routes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use IsmayilDev\ApiDocKit\Entities\Entity;
use IsmayilDev\ApiDocKit\Entities\RoutePathParameter;
use IsmayilDev\ApiDocKit\Helper\ModelHelper;

readonly class RoutePathParameterResolver
{
    private Collection $resolvedParameters;

    public function __construct(private ModelHelper $modelHelper)
    {
        $this->resolvedParameters = collect();
    }

    /**
     * @return Collection<RoutePathParameter>
     */
    public function get(): Collection
    {
        return $this->resolvedParameters;
    }

    public function resolve(array $parameters, Entity $entity): self
    {
        foreach ($parameters as $parameter) {
            $parameterName = strtolower($parameter);

            // Handle primary entity ID
            if ($resolved = $this->resolveFromEntity($parameterName, $entity)) {
                $this->resolvedParameters->push($resolved);

                continue;
            }

            // Handle related model IDs
            if ($resolved = $this->resolveFromRelativeEntity($parameterName, $entity)) {
                $this->resolvedParameters->push($resolved);

                continue;
            }

            // Fallback for unresolved parameters
            $this->resolvedParameters->push($this->getFallbackType($parameterName));
        }

        return $this;
    }

    private function resolveFromEntity(string $parameterName, Entity $entity): ?RoutePathParameter
    {
        $entityKey = strtolower($entity->name());

        if (in_array($parameterName, ['id', $entityKey])) {
            $parameterDescSuffix = $parameterName === $entityKey ? 'ID' : $parameterName;

            return new RoutePathParameter(
                type: $this->mapKeyTypeToOpenApiType($entity->keyType()),
                description: $entity->parameterDescription($parameterDescSuffix),
                example: $entity->exampleId()
            );
        }

        return null;
    }

    private function resolveFromRelativeEntity(string $parameterName, Entity $entity): ?RoutePathParameter
    {
        $relatedModelKey = $parameterName;
        $models = $this->modelHelper->models;

        if (Str::contains($parameterName, 'id', true)) {
            $relatedModelKey = Str::replaceFirst('id', '', $parameterName);
        }

        if (array_key_exists($relatedModelKey, $models)) {
            $relatedModelEntity = $models[$relatedModelKey];

            return new RoutePathParameter(
                type: $this->mapKeyTypeToOpenApiType($relatedModelEntity->keyType()),
                description: $entity->relationParameterDescription($parameterName, $relatedModelEntity),
                example: $relatedModelEntity->exampleId()
            );
        }

        return null;
    }

    private function getFallbackType(string $parameterName): RoutePathParameter
    {
        return new RoutePathParameter(
            type: OpenApiPropertyType::STRING,
            description: "The {$parameterName}",
            example: null,
        );
    }

    private function mapKeyTypeToOpenApiType(string $keyType): OpenApiPropertyType
    {
        return match ($keyType) {
            'int' => OpenApiPropertyType::INTEGER,
            default => OpenApiPropertyType::STRING,
        };
    }
}
