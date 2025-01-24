<?php

namespace BasilLangevin\LaravelDataSchemas\Schemas;

use BasilLangevin\LaravelDataSchemas\Enums\DataType;
use BasilLangevin\LaravelDataSchemas\Keywords\Keyword;
use BasilLangevin\LaravelDataSchemas\Keywords\Object\MaxPropertiesKeyword;
use BasilLangevin\LaravelDataSchemas\Keywords\Object\MinPropertiesKeyword;
use BasilLangevin\LaravelDataSchemas\Keywords\Object\PropertiesKeyword;
use BasilLangevin\LaravelDataSchemas\Keywords\Object\RequiredKeyword;
use BasilLangevin\LaravelDataSchemas\Schemas\Concerns\SingleTypeSchemaTrait;
use BasilLangevin\LaravelDataSchemas\Schemas\Contracts\SingleTypeSchema;

class ObjectSchema implements SingleTypeSchema
{
    use SingleTypeSchemaTrait;

    public static DataType $type = DataType::Object;

    public static array $keywords = [
        Keyword::ANNOTATION_KEYWORDS,
        Keyword::GENERAL_KEYWORDS,
        Keyword::COMPOSITION_KEYWORDS,
        PropertiesKeyword::class,
        RequiredKeyword::class,
        MaxPropertiesKeyword::class,
        MinPropertiesKeyword::class,
    ];
}
