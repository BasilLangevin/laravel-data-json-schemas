<?php

use BasilLangevin\LaravelDataJsonSchemas\Annotators\CustomAnnotationAnnotator;
use BasilLangevin\LaravelDataJsonSchemas\Attributes\CustomAnnotation;
use BasilLangevin\LaravelDataJsonSchemas\Facades\JsonSchema;
use BasilLangevin\LaravelDataJsonSchemas\Tests\TestsSchemaTransformation;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;

covers(CustomAnnotationAnnotator::class);

uses(TestsSchemaTransformation::class);

it('does not apply custom annotations to a schema if the property is not annotated', function () {
    $this->class->addStringProperty('test');

    $schema = $this->class->getSchemaClass();

    expect(fn () => $schema->getProperties()['test']->getCustomAnnotation())
        ->toThrow(Exception::class, 'The keyword "customAnnotation" has not been set.');

    expect(Arr::get($schema->toArray(), 'properties.test'))->toEqual([
        'type' => 'string',
    ]);
});

it('can apply a single custom annotation to a schema')
    ->expect(fn () => $this->class->addStringProperty(
        'test',
        [CustomAnnotation::class => ['foo', 'bar']]
    ))
    ->toHaveSchema('test', [
        'type' => 'string',
        'x-foo' => 'bar',
    ]);

it('can apply multiple custom annotations to a schema', function () {
    class TestApplyMultipleCustomAnnotationsClass extends Data
    {
        public function __construct(
            #[CustomAnnotation('foo', 'bar')]
            #[CustomAnnotation('baz', 'qux')]
            public string $test,
        ) {}
    }

    $schema = JsonSchema::make(TestApplyMultipleCustomAnnotationsClass::class);

    expect($schema->toArray())->toHaveKey('properties.test', [
        'type' => 'string',
        'x-foo' => 'bar',
        'x-baz' => 'qux',
    ]);
});
