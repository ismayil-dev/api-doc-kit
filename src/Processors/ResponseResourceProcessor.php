<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Processors;

use IsmayilDev\ApiDocKit\Attributes\Schema\ResponseResource;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use IsmayilDev\ApiDocKit\Models\DocEntity;
use IsmayilDev\ApiDocKit\Models\ModelMapper;
use IsmayilDev\ApiDocKit\Parsers\ArrayKeyParser;
use OpenApi\Analysis;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use ReflectionException;

class ResponseResourceProcessor
{
    public function __construct(private ModelMapper $modelMapper) {}

    /**
     * @throws ReflectionException
     */
    public function __invoke(Analysis $analysis)
    {
        $annotations = $analysis->annotations;

        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResponseResource) {
                $classWithNamespace = $this->getClassWithNameSpace($annotation);
                $parser = new ArrayKeyParser;
                $keys = $parser->extractKeys($classWithNamespace, 'toArray');
                $mappedModel = $this->modelMapper->newModels[$annotation->getEntity()];
                $schema = collect($mappedModel['schema']);

                $properties = [];
                $required = [];

                foreach ($keys as $key) {
                    $findKeySchema = $schema->first(fn ($item) => $item['name'] === $key);
                    $properties[] = new Property(
                        property: $findKeySchema['name'],
                        description: "The {$findKeySchema['name']}",
                        type: OpenApiPropertyType::mapFromDatabaseType($findKeySchema['type_name'])->value,
                        nullable: $findKeySchema['nullable'],
                    );
                    $required[] = $findKeySchema['name'];
                }

                $entity = new DocEntity($annotation->getEntity());
                $annotation->title = "{$entity->name()} Resource";
                $annotation->description = "Schema for {$entity->name()}";
                $annotation->type = OpenApiPropertyType::OBJECT->value;
                $annotation->properties = $properties;
                $annotation->required = $required;
            }
        }
    }

    protected function getClassWithNameSpace(Schema $annotation): string
    {
        return "{$annotation->_context->namespace}\\{$annotation->_context->class}";
    }
}
