<?php

use BasilLangevin\LaravelDataJsonSchemas\RuleConfigurators\DateRuleConfigurator;
use BasilLangevin\LaravelDataJsonSchemas\Tests\TestsSchemaTransformation;
use Spatie\LaravelData\Attributes\Validation\Date;

covers(DateRuleConfigurator::class);

uses(TestsSchemaTransformation::class);

it('applies the format keyword to a string schema')
    ->expect(fn () => $this->class->addStringProperty('test', [Date::class]))
    ->toHaveSchema('test', [
        'type' => 'string',
        'format' => 'date-time',
    ]);
