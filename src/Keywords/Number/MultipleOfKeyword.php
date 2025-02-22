<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Keywords\Number;

use BasilLangevin\LaravelDataJsonSchemas\Keywords\Keyword;
use Illuminate\Support\Collection;

class MultipleOfKeyword extends Keyword
{
    public function __construct(protected int|float $value) {}

    /**
     * {@inheritdoc}
     */
    public function get(): int|float
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Collection $schema): Collection
    {
        return $schema->merge(['multipleOf' => $this->value]);
    }
}
