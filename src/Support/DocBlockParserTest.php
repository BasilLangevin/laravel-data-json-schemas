<?php

use BasilLangevin\LaravelDataJsonSchemas\Support\DocBlockParser;

covers(DocBlockParser::class);

test('the make method returns a DocBlockParser instance when given a doc block')
    ->expect(DocBlockParser::make('/** @var string $name */'))
    ->toBeInstanceOf(DocBlockParser::class);

test('the make method returns null when given an empty value')
    ->expect(DocBlockParser::make(''))
    ->toBeNull();

test('the make method returns null when given a false value')
    ->expect(DocBlockParser::make(false))
    ->toBeNull();

it('can get a param description')
    ->expect(DocBlockParser::make('/** @param string $name The name of the person */'))
    ->getParamDescription('name')
    ->toBe('The name of the person');

test('getParamDescription returns null when the param is not found')
    ->expect(DocBlockParser::make('/** @param string $name The name of the person */'))
    ->getParamDescription('age')
    ->toBeNull();

it('can get a var description')
    ->expect(DocBlockParser::make('/** @var string $name The name of the person */'))
    ->getVarDescription('name')
    ->toBe('The name of the person');

test('getVarDescription returns null when the var is not found')
    ->expect(DocBlockParser::make('/** @var string $name The name of the person */'))
    ->getVarDescription('age')
    ->toBeNull();

test('getVarDescription returns the first var tag when no name is provided')
    ->expect(DocBlockParser::make('/** @var string $name The name of the person */'))
    ->getVarDescription()
    ->toBe('The name of the person');

it('can get a doc block summary')
    ->expect(DocBlockParser::make('/** This is a test summary. */'))
    ->getSummary()
    ->toBe('This is a test summary.');

it('can get a multi-line summary with no description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary
         * about rabbits.
         */
        DOC))
    ->getSummary()
    ->toBe("This is a test summary\nabout rabbits.");

it('can get a multi-line summary followed by a description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary
         * about rabbits.
         * This is a test description.
         */
        DOC))
    ->getSummary()
    ->toBe("This is a test summary\nabout rabbits.");

it('can get a summary followed by a description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary.
         *
         * This is a test description.
         */
        DOC))
    ->getSummary()
    ->toBe('This is a test summary.');

it('can get a non-full-stopped summary followed by a description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary
         *
         * This is a test description.
         */
        DOC))
    ->getSummary()
    ->toBe('This is a test summary');

it('can get a full-stopped summary followed by a description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary.
         * This is a test description.
         */
        DOC))
    ->getSummary()
    ->toBe('This is a test summary.');

test('getSummary returns null when the doc block has no text nodes')
    ->expect(DocBlockParser::make('/** @param string $name The name of the person */'))
    ->getSummary()
    ->toBeNull();

it('can check if the doc block has a summary')
    ->expect(DocBlockParser::make('/** This is a test summary. */'))
    ->hasSummary()
    ->toBeTrue()
    ->expect(DocBlockParser::make('/** @param string $name The name of the person */'))
    ->hasSummary()
    ->toBeFalse();

it('can get a doc block description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary.
         *
         * This is a test description.
         */
        DOC))
    ->getDescription()
    ->toBe('This is a test description.');

it('can get a doc block description after a full stopped summary')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary.
         * This is a test description.
         */
        DOC))
    ->getDescription()
    ->toBe('This is a test description.');

it('can get a multi-line description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary.
         *
         * This is a test description about rabbits.
         * We like it very much.
         */
        DOC))
    ->getDescription()
    ->toBe("This is a test description about rabbits.\nWe like it very much.");

it('can get a description after a multi-line summary')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary
         * about rabbits.
         * This is a test description.
         */
        DOC))
    ->getDescription()
    ->toBe('This is a test description.');

it('returns null when the doc block has no description')
    ->expect(DocBlockParser::make('/** This is a test summary. */'))
    ->getDescription()
    ->toBeNull();

it('returns null when the doc block has no text nodes')
    ->expect(DocBlockParser::make('/** @param string $name The name of the person */'))
    ->getDescription()
    ->toBeNull();

it('returns null when the doc block has a multi-line summary but no description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary
         * about rabbits.
         */
        DOC))
    ->getDescription()
    ->toBeNull();

it('returns null when the first node of the doc block is not a text node')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * @implements SomeInterface
         *
         * @phpstan-type SomePHPStanType array{key1: int, key2: string}
        */
        DOC))
    ->getDescription()
    ->toBeNull();

it('can check if the doc block has a description')
    ->expect(DocBlockParser::make(<<<'DOC'
        /**
         * This is a test summary.
         *
         * This is a test description.
         */
        DOC))
    ->hasDescription()
    ->toBeTrue()
    ->expect(DocBlockParser::make('/** This is a test summary. */'))
    ->hasDescription()
    ->toBeFalse();

test('getTextNodes returns an array of text nodes', function () {
    $parser = DocBlockParser::make(<<<'DOC'
        /**
         * First text node.
         *
         * Second text node.
         *
         * @var string $name The name of the person
         */
    DOC);

    $reflection = new ReflectionClass($parser);
    $method = $reflection->getMethod('getTextNodes');
    $method->setAccessible(true);

    expect($method->invoke($parser))->toHaveLength(2);
});
