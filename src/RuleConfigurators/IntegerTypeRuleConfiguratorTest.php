<?php

use BasilLangevin\LaravelDataJsonSchemas\RuleConfigurators\IntegerTypeRuleConfigurator;
use BasilLangevin\LaravelDataJsonSchemas\Tests\TestsSchemaTransformation;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

covers(IntegerTypeRuleConfigurator::class);

uses(TestsSchemaTransformation::class);

it('applies the pattern keyword to a string schema')
    ->expect(fn () => $this->class->addStringProperty('test', [IntegerType::class]))
    ->toHaveSchema('test', [
        'type' => 'string',
        'pattern' => '/^[0-9]+$/',
    ]);
