<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Schemas\Concerns;

use BadMethodCallException;
use BasilLangevin\LaravelDataJsonSchemas\Exceptions\KeywordNotSetException;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Annotation\CustomAnnotationKeyword;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Contracts\HandlesMultipleInstances;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Contracts\MergesMultipleInstancesIntoAllOf;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Contracts\ReceivesParentSchema;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Keyword;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasKeywords
{
    /**
     * The instances of each keyword that has been set.
     *
     * @var array<string, array<int, Keyword>>
     */
    private array $keywordInstances = [];

    /**
     * Get the keywords that are available for this schema type.
     *
     * @return array<class-string<Keyword>>
     */
    protected function getKeywords(): array
    {
        if (! property_exists(static::class, 'keywords')) {
            throw new KeywordNotSetException('The keywords property is not set for this schema type.');
        }

        /** @disregard P1014 because the if statement ensures the property exists */
        $keywords = collect(static::$keywords)->flatten();

        if ($keywords->contains(CustomAnnotationKeyword::class)) {
            $keywords->forget($keywords->search(CustomAnnotationKeyword::class));
            $keywords->push(CustomAnnotationKeyword::class);
        }

        /** @var array<int, class-string<Keyword>> $result */
        $result = $keywords->values()->all();

        return $result;
    }

    /**
     * Get the instance of the given keyword.
     *
     * @return array<int, Keyword>
     */
    private function getKeywordInstances(string $name): array
    {
        if (is_subclass_of($name, Keyword::class)) {
            $name = $name::method();
        }

        if (! array_key_exists($name, $this->keywordInstances)) {
            throw new KeywordNotSetException("The keyword \"{$name}\" has not been set.");
        }

        return $this->keywordInstances[$name];
    }

    /**
     * Get the keyword class that has the method matching the given name.
     *
     * @return class-string<Keyword>|null
     */
    private function getKeywordByMethod(string $name): ?string
    {
        return Arr::first($this->getKeywords(), function ($keyword) use ($name) {
            return $keyword::method() === $name;
        });
    }

    /**
     * Check if the keyword method exists among the available keywords.
     */
    private function keywordMethodExists(string $name): bool
    {
        return $this->getKeywordByMethod($name) !== null;
    }

    /**
     * Remove the get prefix from the keyword name.
     */
    private function removeGetPrefix(string $name): string
    {
        $name = str($name)->after('get');

        return $name->substr(0, 1)->lower()
            ->append($name->substr(1));
    }

    /**
     * Check if the keyword getter method exists among the available keywords.
     */
    private function keywordGetterExists(string $name): bool
    {
        if (! Str::startsWith($name, 'get')) {
            return false;
        }

        if (! ctype_upper(Str::charAt($name, 3))) {
            return false;
        }

        $method = $this->removeGetPrefix($name);

        return $this->keywordMethodExists($method);
    }

    /**
     * Set the value for the appropriate keyword.
     *
     * @param  class-string<Keyword>|string  $name
     */
    public function setKeyword(string $name, mixed ...$arguments): self
    {
        if (is_subclass_of($name, Keyword::class)) {
            $keyword = $name;

            /** @var class-string<Keyword> $name */
            $name = $name::method();
        } else {
            $keyword = $this->getKeywordByMethod($name);
        }

        if (! $this->hasKeyword($name)) {
            $this->keywordInstances[$name] = [];
        }

        /** @var class-string<Keyword> $keyword */
        $instance = new $keyword(...$arguments);

        if ($instance instanceof ReceivesParentSchema) {
            $instance->parentSchema($this);
        }

        $this->keywordInstances[$name][] = $instance;

        return $this;
    }

    /**
     * Get the value for the appropriate keyword.
     */
    public function getKeyword(string $name): mixed
    {
        $instances = $this->getKeywordInstances($name);

        $result = collect($instances)->map(function ($instance) {
            return $instance->get();
        });

        if ($result->count() === 1) {
            return $result->first();
        }

        return $result;
    }

    /**
     * Build the base JSON Schema array with the keywords that have been set.
     *
     * @return array<string, mixed>
     */
    public function buildSchema(): array
    {
        /** @var Collection<int, class-string<Keyword>> $keywords */
        $keywords = collect($this->getKeywords())->flatten();

        return $keywords->filter(fn (string $keyword) => $this->hasKeyword($keyword))
            ->reduce(function (Collection $schema, string $keyword) {
                return $this->applyKeyword($keyword, $schema);
            }, collect())
            ->all();
    }

    /**
     * Add the definition for a keyword to the given schema.
     *
     * @param  Collection<string, mixed>  $schema  The schema to add the keyword to.
     * @return Collection<string, mixed>
     */
    public function applyKeyword(string $name, Collection $schema): Collection
    {
        $instances = collect($this->getKeywordInstances($name));

        if ($instances->count() === 1) {
            /** @var Keyword $instance */
            $instance = $instances->first();

            return $instance->apply($schema);
        }

        if (is_subclass_of($name, MergesMultipleInstancesIntoAllOf::class)) {
            /** @var Collection<int, Keyword&MergesMultipleInstancesIntoAllOf> $instances */
            return $this->mergeAllOf($schema, $instances);
        }

        if (is_subclass_of($name, HandlesMultipleInstances::class)) {
            /** @var Collection<int, HandlesMultipleInstances> $instances */
            return $name::applyMultiple($schema, $instances);
        }

        /** @var Keyword $instance */
        $instance = $instances->last();

        return $instance->apply($schema);
    }

    /**
     * Merge the given instances into an allOf keyword.
     *
     * @param  Collection<string, mixed>  $schema  The JSON Schema array to add the keyword to.
     * @param  Collection<int, Keyword&MergesMultipleInstancesIntoAllOf>  $instances  The instances of the keyword to merge.
     * @return Collection<string, mixed>
     */
    protected function mergeAllOf(Collection $schema, Collection $instances): Collection
    {
        /** @var array<string, mixed> $allOf */
        $allOf = $schema->get('allOf', []);

        /** @var Collection<string, mixed> $subschemas */
        $subschemas = $instances->map(function ($instance) {
            return $this->cloneBaseStructure()->setKeyword(get_class($instance), $instance->get());
        })->map->toArray()->unique();

        if (count($subschemas) === 1) {
            /** @var Keyword $instance */
            $instance = $instances->first();

            return $instance->apply($schema);
        }

        $allOf = collect($allOf)->merge($subschemas)->all();

        return $schema->put('allOf', $allOf);
    }

    /**
     * Check if the given keyword has been set.
     */
    public function hasKeyword(string $name): bool
    {
        if (is_subclass_of($name, Keyword::class)) {
            $name = $name::method();
        }

        return array_key_exists($name, $this->keywordInstances);
    }

    /**
     * Allow keyword methods to be called on the schema type.
     *
     * @param  string  $name
     * @param  array<int, mixed>  $arguments
     */
    public function __call(mixed $name, mixed $arguments): mixed
    {
        if ($this->keywordMethodExists($name)) {
            return $this->setKeyword($name, ...$arguments);
        }

        if ($this->keywordGetterExists($name)) {
            return $this->getKeyword($this->removeGetPrefix($name));
        }

        throw new BadMethodCallException("Method \"{$name}\" not found");
    }
}
