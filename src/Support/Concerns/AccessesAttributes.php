<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Support\Concerns;

use BasilLangevin\LaravelDataJsonSchemas\Support\AttributeWrapper;
use BasilLangevin\LaravelDataJsonSchemas\Support\ClassWrapper;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

trait AccessesAttributes
{
    /**
     * Get the attributes of the property as a collection of AttributeWrappers.
     *
     * @template T of object
     *
     * @param  class-string<T>|null  $name
     * @return Collection<int, AttributeWrapper>
     */
    public function attributes(?string $name = null): Collection
    {
        /** @var \ReflectionClass<Data>|\ReflectionProperty $reflector */
        $reflector = $this instanceof ClassWrapper ? $this->class : $this->property;

        /** @var array<int, \ReflectionAttribute<T>> $attributes */
        $attributes = $reflector->getAttributes($name);

        return collect($attributes)
            ->filter(fn (\ReflectionAttribute $attribute): bool => AttributeWrapper::supports($attribute))
            ->map(fn (\ReflectionAttribute $attribute): AttributeWrapper => new AttributeWrapper($attribute));
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $name
     */
    public function hasAttribute(string $name): bool
    {
        return $this->attributes($name)->isNotEmpty();
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $name
     */
    public function getAttribute(string $name): AttributeWrapper|Collection|null
    {
        $attributes = $this->attributes($name);

        if ($attributes->isEmpty()) {
            return null;
        }

        if ($attributes->count() === 1) {
            return $attributes->first();
        }

        return $attributes;
    }
}
