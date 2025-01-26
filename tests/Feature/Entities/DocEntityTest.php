<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Entities;

use IsmayilDev\ApiDocKit\Entities\DocEntity;
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
