<?php

use BasilLangevin\LaravelDataJsonSchemas\RuleConfigurators\TimezoneRuleConfigurator;
use BasilLangevin\LaravelDataJsonSchemas\Tests\TestsSchemaTransformation;
use Spatie\LaravelData\Attributes\Validation\Timezone;

covers(TimezoneRuleConfigurator::class);

uses(TestsSchemaTransformation::class);

it('applies the x-timezone keyword to a string schema')
    ->expect(fn () => $this->class->addStringProperty('test', [Timezone::class]))
    ->toHaveSchema('test', [
        'type' => 'string',
        'x-timezone' => 'The value must be a timezone.',
    ]);
