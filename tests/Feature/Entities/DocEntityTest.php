<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Entities;

use IsmayilDev\ApiDocKit\Models\DocEntity;
use IsmayilDev\ApiDocKit\Tests\Doubles\Models\User;

describe('DocEntity Test', function () {

    beforeEach(function () {
        $this->docEntity = new DocEntity(User::class);
    });

    it('should be instantiable', function () {
        $this->assertInstanceOf(DocEntity::class, $this->docEntity);
    });

    it('should return correct name', function () {
        expect($this->docEntity->name())->toBe('User');
    });

    it('should return correct plural name', function () {
        expect($this->docEntity->getPluralName())->toBe('Users');
    });

    it('should return correct description', function () {
        expect($this->docEntity->description('Get'))->toBe('Get User')
            ->and($this->docEntity->description('Get', true))->toBe('Get Users');
    });

    it('should return tags', function () {
        expect($this->docEntity->tags())->toBe(['Users'])
            ->and($this->docEntity->tags(['Test']))->toBe(['Users', 'Test']);
    });

    it('should return example id', function () {
        expect($this->docEntity->exampleId())->toBe('5');
    })->todo('Refactor later');

    test('getEntity', function () {})->todo('implement');
});

describe('DocEntity with Static Strings', function () {

    it('should work with static string entity', function () {
        $entity = new DocEntity('Product');

        expect($entity->name())->toBe('Product')
            ->and($entity->getPluralName())->toBe('Products')
            ->and($entity->description('Get'))->toBe('Get Product')
            ->and($entity->tags())->toBe(['Products']);
    });

    it('should use default int key type for static strings', function () {
        $entity = new DocEntity('Product');

        expect($entity->keyType())->toBe('int');
    });

    it('should generate example ID from entity name for static strings', function () {
        $entity = new DocEntity('Product');

        expect($entity->exampleId())->toBe('product-id');
    });

    it('should allow custom key type override', function () {
        $entity = new DocEntity('Product', keyType: 'uuid');

        expect($entity->keyType())->toBe('uuid');
    });

    it('should allow custom example ID override', function () {
        $entity = new DocEntity('Product', exampleId: '550e8400-e29b-41d4-a716-446655440000');

        expect($entity->exampleId())->toBe('550e8400-e29b-41d4-a716-446655440000');
    });

    it('should work with integer example ID', function () {
        $entity = new DocEntity('Product', exampleId: 123);

        expect($entity->exampleId())->toBe(123);
    });

    it('should allow both key type and example ID overrides', function () {
        $entity = new DocEntity(
            entity: 'Product',
            keyType: 'string',
            exampleId: 'abc-123'
        );

        expect($entity->keyType())->toBe('string')
            ->and($entity->exampleId())->toBe('abc-123');
    });

    it('should not throw exception when calling exampleId on static string', function () {
        $entity = new DocEntity('Product');

        // Should work fine - static strings have default example ID generation
        expect($entity->exampleId())->toBe('product-id');
    });
});
