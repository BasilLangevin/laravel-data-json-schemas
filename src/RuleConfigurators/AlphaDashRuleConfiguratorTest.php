<?php

use BasilLangevin\LaravelDataJsonSchemas\RuleConfigurators\AlphaDashRuleConfigurator;
use BasilLangevin\LaravelDataJsonSchemas\Tests\TestsSchemaTransformation;
use Spatie\LaravelData\Attributes\Validation\AlphaDash;

covers(AlphaDashRuleConfigurator::class);

uses(TestsSchemaTransformation::class);

it('applies the pattern keyword to a string schema')
    ->expect(fn () => $this->class->addStringProperty('test', [AlphaDash::class]))
    ->toHaveSchema('test', [
        'type' => 'string',
        'pattern' => '/^[a-zA-Z0-9_-]+$/',
    ]);
