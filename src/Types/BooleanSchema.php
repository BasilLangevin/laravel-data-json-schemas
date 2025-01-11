<?php

namespace BasilLangevin\LaravelDataSchemas\Types;

use BasilLangevin\LaravelDataSchemas\Enums\DataType;
use BasilLangevin\LaravelDataSchemas\Keywords\DefaultKeyword;
use BasilLangevin\LaravelDataSchemas\Keywords\DescriptionKeyword;
use BasilLangevin\LaravelDataSchemas\Keywords\EnumKeyword;
use BasilLangevin\LaravelDataSchemas\Keywords\FormatKeyword;
use BasilLangevin\LaravelDataSchemas\Keywords\TitleKeyword;

class BooleanSchema extends Schema
{
    public static DataType $type = DataType::Boolean;

    public static array $keywords = [
        TitleKeyword::class,
        DescriptionKeyword::class,
        FormatKeyword::class,
        EnumKeyword::class,
        DefaultKeyword::class,
    ];
}
