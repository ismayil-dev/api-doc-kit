<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Routes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use IsmayilDev\ApiDocKit\Entities\DocEntity;
use IsmayilDev\ApiDocKit\Entities\RoutePathParameter;
use IsmayilDev\ApiDocKit\Mappers\ModelMapper;

readonly class RoutePathParameterResolver
{
    private Collection $resolvedParameters;

    public function __construct(private ModelMapper $modelMapper)
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

    /**
     * @param  array<int, array{name: string, optional: bool}>  $parameters
     */
    public function resolve(array $parameters, DocEntity $entity): self
    {
        foreach ($parameters as $parameter) {
            $parameter['name'] = strtolower($parameter['name']);

            // Handle primary entity ID
            if ($resolved = $this->resolveFromEntity($parameter, $entity)) {
                $this->resolvedParameters->push($resolved);

                continue;
            }

            // Handle related model IDs
            if ($resolved = $this->resolveFromRelativeEntity($parameter, $entity)) {
                $this->resolvedParameters->push($resolved);

                continue;
            }

            // Fallback for unresolved parameters
            $this->resolvedParameters->push($this->getFallbackType($parameter));
        }

        return $this;
    }

    private function resolveFromEntity(array $parameter, DocEntity $entity): ?RoutePathParameter
    {
        $parameterName = $parameter['name'];
        $entityKey = strtolower($entity->name());

        if (in_array($parameterName, ['id', $entityKey])) {
            $parameterDescSuffix = $parameterName === $entityKey ? 'ID' : $parameterName;

            return new RoutePathParameter(
                name: $parameterName,
                type: $this->mapKeyTypeToOpenApiType($entity->keyType()),
                description: $entity->parameterDescription($parameterDescSuffix),
                example: $entity->exampleId(),
                optional: $parameter['optional'],
            );
        }

        return null;
    }

    private function resolveFromRelativeEntity(array $parameter, DocEntity $entity): ?RoutePathParameter
    {
        $parameterName = $parameter['name'];
        $relatedModelKey = $parameterName;
        $models = $this->modelMapper->models;

        if (Str::contains($parameterName, 'id', true)) {
            $relatedModelKey = Str::replaceFirst('id', '', $parameterName);
        }

        if (array_key_exists($relatedModelKey, $models)) {
            $relatedModelEntity = $models[$relatedModelKey];

            return new RoutePathParameter(
                name: $parameterName,
                type: $this->mapKeyTypeToOpenApiType($relatedModelEntity->keyType()),
                description: $entity->relationParameterDescription($parameterName, $relatedModelEntity),
                example: $relatedModelEntity->exampleId(),
                optional: $parameter['optional'],
            );
        }

        return null;
    }

    private function getFallbackType(array $parameter): RoutePathParameter
    {
        $parameterName = $parameter['name'];

        return new RoutePathParameter(
            name: $parameterName,
            type: OpenApiPropertyType::STRING,
            description: "The {$parameterName}",
            example: null,
            optional: $parameter['optional'],
        );
    }

    private function mapKeyTypeToOpenApiType(string $keyType): OpenApiPropertyType
    {
        return match ($keyType) {
            'int', 'integer' => OpenApiPropertyType::INTEGER,
            default => OpenApiPropertyType::STRING,
        };
    }
}
