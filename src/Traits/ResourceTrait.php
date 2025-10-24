<?php

namespace IsmayilDev\ApiDocKit\Traits;

use IsmayilDev\ApiDocKit\Attributes\Responses\ErrorResponses;
use OpenApi\Attributes\MediaType;

trait ResourceTrait
{
    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getRequestClass(): ?string
    {
        return $this->requestClass;
    }

    public function getActionName(): ?string
    {
        return $this->actionName;
    }

    public function getResponseEntity(): ?string
    {
        return $this->responseEntity;
    }

    public function getErrorResponses(): ?ErrorResponses
    {
        return $this->errorResponses ?? null;
    }

    public function getSuccessResponseSchema(): MediaType|string|null
    {
        return $this->successResponseSchema ?? null;
    }

    public function getErrorResponseSchemas(): ?array
    {
        return $this->errorResponseSchemas ?? null;
    }

    public function getKeyType(): ?string
    {
        return $this->keyType ?? null;
    }

    public function getExampleId(): string|int|null
    {
        return $this->exampleId ?? null;
    }
}
