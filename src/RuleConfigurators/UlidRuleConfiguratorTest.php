<?php

use BasilLangevin\LaravelDataJsonSchemas\RuleConfigurators\UlidRuleConfigurator;
use BasilLangevin\LaravelDataJsonSchemas\Tests\TestsSchemaTransformation;
use Spatie\LaravelData\Attributes\Validation\Ulid;

covers(UlidRuleConfigurator::class);

uses(TestsSchemaTransformation::class);

it('applies the x-ulid keyword to a string schema')
    ->expect(fn () => $this->class->addStringProperty('test', [Ulid::class]))
    ->toHaveSchema('test', [
        'type' => 'string',
        'x-ulid' => 'The value must be a valid ULID.',
    ]);
