<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Support;

use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\AccessesAttributes;
use BasilLangevin\LaravelDataJsonSchemas\Support\Concerns\AccessesDocBlock;
use BasilLangevin\LaravelDataJsonSchemas\Support\Contracts\EntityWrapper;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Factories\DataClassFactory;

/**
 * The ClassWrapper provides convenient access to the
 * properties, methods, and doc blocks of a Spatie
 * Data class, simplifying schema construction.
 */
class ClassWrapper implements EntityWrapper
{
    use AccessesAttributes;
    use AccessesDocBlock;

    /**
     * @param  ReflectionClass<Data>  $class
     */
    public function __construct(protected ReflectionClass $class)
    {
        if (! $class->isSubclassOf(Data::class)) {
            throw new \InvalidArgumentException('Only data classes are supported.');
        }
    }

    /**
     * Create a new class wrapper from a class name.
     *
     * @param  class-string<Data>  $className
     */
    public static function make(string $className): self
    {
        return new self(new ReflectionClass($className));
    }

    /**
     * Get the Spatie DataClass instance for the class.
     */
    public function getDataClass(): DataClass
    {
        return app(DataClassFactory::class)->build($this->class);
    }

    /**
     * Get the name of the class.
     */
    public function getName(): string
    {
        return $this->class->getName();
    }

    /**
     * Get the short name of the class.
     */
    public function getShortName(): string
    {
        return $this->class->getShortName();
    }

    /**
     * Get the properties of the class as a collection of PropertyWrapper instances.
     *
     * @return Collection<int, PropertyWrapper>
     */
    public function properties(): Collection
    {
        return $this->getDataClass()->properties
            ->map(function (DataProperty $property) {
                return new PropertyWrapper($property);
            })
            ->values();
    }

    /**
     * Get a property wrapper for the given property name.
     */
    public function getProperty(string $propertyName): PropertyWrapper
    {
        return $this->properties()
            ->first(fn (PropertyWrapper $property) => $property->getName() === $propertyName)
            ?? throw new \Exception("Property \"{$propertyName}\" not found in class \"{$this->getName()}\"");
    }

    /**
     * Check if the class has a constructor.
     */
    public function hasConstructor(): bool
    {
        return $this->class->hasMethod('__construct');
    }

    /**
     * Get the constructor of the class.
     */
    public function getConstructor(): ReflectionMethod
    {
        return $this->class->getMethod('__construct');
    }

    /**
     * Get the doc block for the class' __construct method.
     */
    public function getConstructorDocBlock(): ?DocBlockParser
    {
        if (! $this->hasConstructor()) {
            return null;
        }

        $docComment = $this->getConstructor()->getDocComment();

        return DocBlockParser::make($docComment);
    }
}
