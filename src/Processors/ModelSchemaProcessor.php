<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Processors;

use Illuminate\Database\Eloquent\Model;
use IsmayilDev\ApiDocKit\Attributes\Schema\ModelSchema;
use IsmayilDev\ApiDocKit\Resolvers\ModelSchemaResolver;
use OpenApi\Analysis;
use OpenApi\Annotations\Operation;

//@TODO GET RID OF THIS CLASS
class ModelSchemaProcessor
{
    public function __construct(protected ModelSchemaResolver $modelSchemaResolver) {}

    public function __invoke(Analysis $analysis): void
    {
        $annotations = $analysis->annotations;
        $annotationsToDetach = [];

        foreach ($annotations as $annotation) {
            if ($annotation instanceof ModelSchema) {

                $classWithNamespace = $this->getClassWithNameSpace($annotation);
                /** @var Model $model */
                $model = new $classWithNamespace;
                //                $this->modelSchemaResolver->resolve($model);

                $annotationsToDetach[] = $annotation;
            }
        }

        foreach ($annotationsToDetach as $itemToDetach) {
            $annotations->detach($itemToDetach);
        }
    }

    protected function getClassWithNameSpace(Operation $annotation): string
    {
        return "{$annotation->_context->namespace}\\{$annotation->_context->class}";
    }
}
