<?php

namespace IsmayilDev\ApiDocKit\Traits;

trait ResourceTrait
{
    public function getModel(): string
    {
        return $this->model;
    }

    public function getRequestClass(): ?string
    {
        return $this->requestClass;
    }

    public function getActionName(): ?string
    {
        return $this->actionName;
    }
}
