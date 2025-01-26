<?php

namespace IsmayilDev\ApiDocKit\Traits;

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

    public function isList(): bool
    {
        return $this->isList;
    }
}
