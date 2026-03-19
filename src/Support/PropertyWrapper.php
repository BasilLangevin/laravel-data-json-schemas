<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Support;

use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\AccessesAttributes;
use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\AccessesDocBlock;
use BasilLangevin\LaravelDataJsonSchemas\Support\Contracts\EntityWrapper;
use DateTimeInterface;
use Illuminate\Support\Collection;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\DataPropertyType;
use Spatie\LaravelData\Support\Types\CombinationType;
use Spatie\LaravelData\Support\Types\NamedType;
use Spatie\LaravelData\Support\Types\Type;

/**
 * The PropertyWrapper provides convenient access to the types,
 * name, default value, sibling properties, and Data classes
 * of a property, supporting the construction of Schemas.
 */
class PropertyWrapper implements EntityWrapper
{
    use AccessesAttributes;
    use AccessesDocBlock;

    protected ReflectionProperty $property;

    public function __construct(
        protected DataProperty $dataProperty
    ) {
        $this->property = new ReflectionProperty($dataProperty->className, $dataProperty->name);
    }

    /**
     * Create a new property wrapper from a reflection property.
     *
     * @param  class-string<Data>  $className
     */
    public static function make(string $className, string $propertyName): self
    {
        return ClassWrapper::make($className)->getProperty($propertyName);
    }

    /**
     * Get the ReflectionProperty instance.
     */
    public function getReflection(): ReflectionProperty
    {
        return $this->property;
    }

    /**
     * Get the Spatie DataProperty instance.
     */
    public function getDataProperty(): DataProperty
    {
        return $this->dataProperty;
    }

    /**
     * Get the DataPropertyType of the DataProperty instance.
     */
    public function getDataType(): DataPropertyType
    {
        return $this->dataProperty->type;
    }

    /**
     * Get the ReflectionType of the property.
     */
    public function getReflectionType(): ReflectionType
    {
        // The PropertyWrapper doesn't support typeless properties.
        /** @var ReflectionType $type */
        $type = $this->property->getType();

        return $type;
    }

    /**
     * Get the named reflection types of the property as a collection.
     *
     * Union and intersection types are flattened into their constituent named types.
     *
     * @return Collection<int, ReflectionNamedType>
     */
    public function getReflectionTypes(): Collection
    {
        $type = $this->getReflectionType();

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            /** @var array<int, ReflectionNamedType> $types */
            $types = $type->getTypes();

            return collect($types);
        }

        /** @var ReflectionNamedType $type */
        return collect([$type]);
    }

    /**
     * Get the Spatie Type of the property.
     */
    public function getType(): Type
    {
        return $this->getDataType()->type;
    }

    /**
     * Get the Spatie Types of the property as a collection.
     *
     * @return Collection<int, NamedType>
     */
    public function getTypes(): Collection
    {
        $type = $this->getType();

        if ($type instanceof CombinationType) {
            /** @var array<int, NamedType> $types */
            $types = $type->types;

            return collect($types);
        }

        /** @var NamedType $type */
        return Collection::make([$type]);
    }

    /**
     * Get the names of the Spatie Types of the property as a collection.
     *
     * @return Collection<int, string>
     */
    public function getTypeNames(): Collection
    {
        return $this->getTypes()->map->name;
    }

    /**
     * Determine if the property has a type.
     */
    public function hasType(string $type): bool
    {
        return match ($type) {
            '*' => true,
            'array' => $this->hasTypeName('array'),
            'boolean' => $this->hasTypeName('bool'),
            'integer' => $this->hasTypeName('int'),
            'number' => $this->hasTypeName('int') || $this->hasTypeName('float'),
            'object' => $this->hasTypeName('object'),
            'string' => $this->hasTypeName('string'),
            default => false,
        };
    }

    /**
     * Determine if the property has a type name.
     */
    public function hasTypeName(string $type): bool
    {
        return $this->getTypeName() === $type;
    }

    /**
     * Get the name of the type of the property.
     */
    public function getTypeName(): ?string
    {
        $type = $this->getType();

        if ($type instanceof NamedType) {
            return $type->name;
        }

        return null;
    }

    /**
     * Determine if the property is a DateTime.
     */
    public function isDateTime(): bool
    {
        $typeName = $this->getTypeName();

        if (is_null($typeName)) {
            return false; // @pest-mutate-ignore Strict type assertion for PHPStan.
        }

        return is_subclass_of($typeName, DateTimeInterface::class)
            || $typeName === 'DateTimeInterface';
    }

    /**
     * Determine if the property is an array.
     */
    public function isArray(): bool
    {
        $type = $this->getType();

        if (! $type instanceof NamedType) {
            return false;
        }

        $kind = $type->kind;

        return $kind->isDataCollectable() || $kind->isNonDataIteratable();
    }

    /**
     * Determine if the property is an enum.
     */
    public function isEnum(): bool
    {
        $typeName = $this->getTypeName();

        if (is_null($typeName)) {
            return false; // @pest-mutate-ignore Strict type assertion for PHPStan.
        }

        return enum_exists($typeName);
    }

    /**
     * Determine if the property is a Spatie data object.
     */
    public function isDataObject(): bool
    {
        $type = $this->getType();

        if ($this->isArray()) {
            return false;
        }

        if (! $type instanceof NamedType) {
            return false;
        }

        if (is_null($type->dataClass)) {
            return false; // @pest-mutate-ignore Strict type assertion for PHPStan.
        }

        return is_subclass_of($type->dataClass, Data::class);
    }

    /**
     * Determine if the property is nullable.
     */
    public function isNullable(): bool
    {
        return $this->getDataType()->isNullable;
    }

    /**
     * Get the name of the data class of the property.
     *
     * @return class-string<Data>|null
     */
    public function getDataClassName(): ?string
    {
        $type = $this->getType();

        if (! $type instanceof NamedType) {
            return null;
        }

        /** @var class-string<Data> */
        return $type->dataClass;
    }

    /**
     * Get the data class of the property as a ClassWrapper.
     */
    public function getDataClass(): ?ClassWrapper
    {
        if (! $this->isDataObject()) {
            return null;
        }

        /** @var class-string<Data> */
        $dataClassName = $this->getDataClassName();

        return ClassWrapper::make($dataClassName);
    }

    /**
     * Get the name of the property.
     */
    public function getName(): string
    {
        return $this->property->getName();
    }

    /**
     * Determine if the property has a default value.
     */
    public function hasDefaultValue(): bool
    {
        return $this->property->hasDefaultValue();
    }

    /**
     * Get the default value of the property.
     */
    public function getDefaultValue(): mixed
    {
        return $this->property->getDefaultValue();
    }

    /**
     * Get the declaring class of the property as a ClassWrapper.
     */
    public function getClass(): ClassWrapper
    {
        return new ClassWrapper($this->property->getDeclaringClass());
    }

    /**
     * Get the siblings of the property as a collection.
     *
     * @return Collection<int, PropertyWrapper>
     */
    public function siblings(): Collection
    {
        return collect($this->getClass()->properties())
            ->filter(function (PropertyWrapper $property) {
                return $property->getName() !== $this->getName();
            })
            ->values();
    }

    /**
     * Get the sibling names of the property as a collection.
     *
     * @return Collection<int, string>
     */
    public function siblingNames(): Collection
    {
        return $this->siblings()->map->getName();
    }
}
