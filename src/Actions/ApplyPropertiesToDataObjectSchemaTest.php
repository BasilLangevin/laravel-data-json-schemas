<?php

use BasilLangevin\LaravelDataJsonSchemas\Actions\ApplyPropertiesToDataObjectSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\ObjectSchema;
use BasilLangevin\LaravelDataJsonSchemas\Support\ClassWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Support\SchemaTree;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

covers(ApplyPropertiesToDataObjectSchema::class);

beforeEach(function () {
    $this->tree = app(SchemaTree::class);
});

class ApplyPropertiesToDataObjectSchemaTestClass extends Data
{
    public function __construct(
        public string $requiredString,
        public string $stringWithDefault,
        public int $requiredInt,
        public ?int $optionalInt = null,
        #[Present]
        public ?string $presentAttribute = null,
        #[Required]
        public ?string $requiredAttribute = 'optional',
    ) {}
}

it('adds properties to the schema', function () {
    $class = ClassWrapper::make(ApplyPropertiesToDataObjectSchemaTestClass::class);

    $schema = ObjectSchema::make();
    ApplyPropertiesToDataObjectSchema::run($schema, $class, $this->tree);

    expect($schema->getProperties())->toHaveCount(6);
    expect(collect($schema->getProperties())->keys()->toArray())->toBe([
        'requiredString',
        'stringWithDefault',
        'requiredInt',
        'optionalInt',
        'presentAttribute',
        'requiredAttribute',
    ]);
});

it('does not add properties to the schema if there are no properties', function () {
    class NoPropertiesDataClass extends Data {}

    $class = ClassWrapper::make(NoPropertiesDataClass::class);

    $schema = ObjectSchema::make();
    ApplyPropertiesToDataObjectSchema::run($schema, $class, $this->tree);

    expect(fn () => $schema->getProperties())->toThrow(Exception::class, 'The keyword "properties" has not been set.');
});
