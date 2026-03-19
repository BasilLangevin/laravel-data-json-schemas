<?php

use BasilLangevin\LaravelDataJsonSchemas\Attributes\CustomAnnotation;

covers(CustomAnnotation::class);

it('can be instantiated')
    ->expect(fn () => new CustomAnnotation('annotation', 'value'))
    ->toBeInstanceOf(CustomAnnotation::class);

it('can be instantiated with an array')
    ->expect(new CustomAnnotation(['annotation' => 'value']))
    ->toBeInstanceOf(CustomAnnotation::class);

it('throws an exception if instantiated with a string without a value', function () {
    new CustomAnnotation('annotation');
})->throws(InvalidArgumentException::class, 'Custom annotations require a key and value.');

it('can get its value')
    ->expect(new CustomAnnotation('annotation', 'value'))
    ->getValue()
    ->toBe(['annotation' => 'value']);

it('can get its value with an array')
    ->expect(new CustomAnnotation(['annotation' => 'value']))
    ->getValue()
    ->toBe(['annotation' => 'value']);
