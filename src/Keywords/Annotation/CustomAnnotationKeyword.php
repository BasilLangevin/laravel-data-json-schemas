<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Keywords\Annotation;

use BasilLangevin\LaravelDataJsonSchemas\Keywords\Contracts\HandlesMultipleInstances;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Keyword;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CustomAnnotationKeyword extends Keyword implements HandlesMultipleInstances
{
    /**
     * @param  string|array<string, string>  $annotation
     */
    public function __construct(protected string|array $annotation, protected ?string $value = null) {}

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>
     */
    public function get(): array
    {
        /** @var array<string, string> $annotations */
        $annotations = is_array($this->annotation)
            ? $this->annotation
            : [$this->annotation => $this->value];

        return collect($annotations)
            ->mapWithKeys(fn ($value, $key) => [Str::start($key, 'x-') => $value])
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Collection $schema): Collection
    {
        return $schema->merge($this->get());
    }

    /**
     * {@inheritdoc}
     */
    public static function applyMultiple(Collection $schema, Collection $instances): Collection
    {
        /** @var Collection<string, string> $annotations */
        $annotations = $instances->flatMap->get();

        return $schema->merge($annotations);
    }
}
