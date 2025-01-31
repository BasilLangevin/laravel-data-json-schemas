<?php

namespace BasilLangevin\LaravelDataSchemas\Schemas\DocBlockAnnotations;

/**
 * @method static multipleOf(int|float $multipleOf) Set the multipleOf keyword of the schema.
 * @method int|float|Collection<int, int|float> getMultipleOf() Get the value(s) passed to the multipleOf method.
 *
 * @see BasilLangevin\LaravelDataSchemas\Keywords\Number\MultipleOfKeyword
 *
 * @method static maximum(int|float $maximum) Set the maximum keyword of the schema.
 * @method int|float|Collection<int, int|float> getMaximum() Get the value(s) passed to the maximum method.
 *
 * @see BasilLangevin\LaravelDataSchemas\Keywords\Number\MaximumKeyword
 *
 * @method static exclusiveMaximum(int|float $exclusiveMaximum) Set the exclusiveMaximum keyword of the schema.
 * @method int|float|Collection<int, int|float> getExclusiveMaximum() Get the value(s) passed to the exclusiveMaximum method.
 *
 * @see BasilLangevin\LaravelDataSchemas\Keywords\Number\ExclusiveMaximumKeyword
 *
 * @method static minimum(int|float $minimum) Set the minimum keyword of the schema.
 * @method int|float|Collection<int, int|float> getMinimum() Get the value(s) passed to the minimum method.
 *
 * @see BasilLangevin\LaravelDataSchemas\Keywords\Number\MinimumKeyword
 *
 * @method static exclusiveMinimum(int|float $exclusiveMinimum) Set the exclusiveMinimum keyword of the schema.
 * @method int|float|Collection<int, int|float> getExclusiveMinimum() Get the value(s) passed to the exclusiveMinimum method.
 *
 * @see BasilLangevin\LaravelDataSchemas\Keywords\Number\ExclusiveMinimumKeyword
 */
trait NumberSchemaKeywordMethodAnnotations {}
