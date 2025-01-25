<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Entities;

use IsmayilDev\ApiDocKit\Entities\Entity;
use IsmayilDev\ApiDocKit\Tests\Doubles\Models\User;

describe('Entity', function () {

    beforeEach(function () {
        $this->entity = new Entity(User::class);
    });

    it('should be instantiable', function () {
        $this->assertInstanceOf(Entity::class, $this->entity);
    });

    it('should return correct name', function () {
        expect($this->entity->name())->toBe('User');
    });

    it('should return correct plural name', function () {
        expect($this->entity->getPluralName())->toBe('Users');
    });

    it('should return correct description', function () {
        expect($this->entity->description('Get'))->toBe('Get User')
            ->and($this->entity->description('Get', true))->toBe('Get Users');
    });

    it('should return tags', function () {
        expect($this->entity->tags())->toBe(['Users'])
            ->and($this->entity->tags(['Test']))->toBe(['Users', 'Test']);
    });

    it('should return example id', function () {
        expect($this->entity->exampleId())->toBe('5');
    })->todo('Refactor later');

    test('getEntity', function () {})->todo('implement');
});
