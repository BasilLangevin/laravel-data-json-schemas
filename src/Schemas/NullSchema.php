<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Schemas;

use BasilLangevin\LaravelDataJsonSchemas\Enums\DataType;
use BasilLangevin\LaravelDataJsonSchemas\Keywords\Keyword;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\Concerns\SingleTypeSchemaTrait;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\Contracts\SingleTypeSchema;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\AnnotationKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\CompositionKeywordMethodAnnotations;
use BasilLangevin\LaravelDataJsonSchemas\Schemas\DocBlockAnnotations\GeneralKeywordMethodAnnotations;

class NullSchema implements SingleTypeSchema
{
    use AnnotationKeywordMethodAnnotations;
    use CompositionKeywordMethodAnnotations;
    use GeneralKeywordMethodAnnotations;
    use SingleTypeSchemaTrait;

    public static DataType $type = DataType::Null;

    /**
     * @var array<class-string<Keyword>|array<class-string<Keyword>>>
     */
    public static array $keywords = [
        Keyword::ANNOTATION_KEYWORDS,
        Keyword::GENERAL_KEYWORDS,
        Keyword::COMPOSITION_KEYWORDS,
    ];
}
