<?php

use BasilLangevin\LaravelDataJsonSchemas\Enums\DataType;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Composition\NotKeyword;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\StringSchema;
use Mockery\MockInterface;

covers(NotKeyword::class);

$basicOutput = collect([
    'type' => DataType::String->value,
]);

$notCallback = fn (StringSchema $schema) => $schema->minLength(42);
$notCallback2 = fn (StringSchema $schema) => $schema->minLength(43);

it('can set a callback')
    ->expect(fn () => StringSchema::make()->not($notCallback))
    ->getNot()->toBe($notCallback);

it('can get its callback')
    ->expect(StringSchema::make()->not($notCallback))
    ->getNot()->toBe($notCallback);

it('can set its parent schema', function () use ($notCallback) {
    $not = new NotKeyword($notCallback);

    $parentSchema = StringSchema::make();

    expect($not->parentSchema($parentSchema))->toBe($not);

    $reflection = new ReflectionProperty(NotKeyword::class, 'parentSchema');
    $reflection->setAccessible(true);

    expect($reflection->getValue($not))->toBe($parentSchema);
});

test('its parent schema is automatically set when calling the keyword', function () use ($notCallback) {
    $parentSchema = StringSchema::make();

    $parentSchema->not($notCallback);

    $reflectionObject = new ReflectionObject($parentSchema);
    $reflectionObject->getMethod('getKeywordInstances')->setAccessible(true);
    $notInstances = $reflectionObject->getMethod('getKeywordInstances')->invoke($parentSchema, NotKeyword::class);

    $not = $notInstances[0];

    $reflectionProperty = new ReflectionProperty(NotKeyword::class, 'parentSchema');
    $reflectionProperty->setAccessible(true);

    expect($reflectionProperty->getValue($not))->toBe($parentSchema);
});

it('can apply the callback to a schema', function () use ($notCallback) {
    $schema = StringSchema::make()->not($notCallback);

    expect($schema->toArray())
        ->toEqual([
            'not' => [
                'minLength' => 42,
            ],
        ]);
});

it('passes the nested flag when applying the callback to a schema', function () use ($basicOutput) {
    $partialMock = $this->partialMock(StringSchema::class, function (MockInterface $mock) {
        $mock->shouldReceive('cloneBaseStructure')
            ->once()
            ->andReturnSelf();

        $mock->shouldReceive('toArray')
            ->with(true)
            ->once();
    });

    $schema = $partialMock->not(fn () => null);

    $schema->applyKeyword(NotKeyword::class, $basicOutput);

});

it('combines multiple not keywords into an allOf')
    ->expect(fn () => StringSchema::make()->not($notCallback)->not($notCallback2))
    ->applyKeyword(NotKeyword::class, $basicOutput)
    ->toEqual(collect([
        'type' => DataType::String->value,
        'allOf' => [
            ['not' => ['minLength' => 42]],
            ['not' => ['minLength' => 43]],
        ],
    ]));
