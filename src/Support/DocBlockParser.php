<?php

namespace BasilLangevin\LaravelDataJsonSchemas\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

/**
 * The DocBlockParser helps with extracting information from PHPDoc blocks.
 */
class DocBlockParser
{
    protected PhpDocNode $phpDocNode;

    /**
     * Create a new DocBlockTransformer instance, parsing the PHPDoc
     * and extracting the param tags.
     *
     * @see https://github.com/phpstan/phpdoc-parser
     */
    public function __construct(protected string $docComment)
    {
        $config = new ParserConfig(usedAttributes: []);
        $lexer = new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);
        $phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);

        $tokens = new TokenIterator($lexer->tokenize($this->docComment));
        $this->phpDocNode = $phpDocParser->parse($tokens);
    }

    /**
     * Create a new DocBlockParser instance.
     */
    public static function make(string|false $docComment): ?self
    {
        if (empty($docComment)) {
            return null;
        }

        return new self($docComment);
    }

    /**
     * Get the children of the doc block.
     *
     * @return \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode[]
     */
    protected function getChildren(): array
    {
        return $this->phpDocNode->children;
    }

    /**
     * Get the text nodes of the doc block.
     *
     * @return \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode[]
     */
    protected function getTextNodes(): array
    {
        return array_filter(
            $this->getChildren(),
            fn ($child) => $child instanceof PhpDocTextNode
        );
    }

    /**
     * Get the first text node of the doc block.
     */
    protected function getFirstTextNode(): ?PhpDocTextNode
    {
        return Arr::first($this->getTextNodes());
    }

    /**
     * Get the summary of the doc block.
     */
    public function getSummary(): ?string
    {
        if (empty($this->getTextNodes())) {
            return null;
        }

        $content = str($this->getFirstTextNode()?->text ?? '');

        if ($content->contains("\n\n")) {
            $content = $content->before("\n\n")->trim();
        }

        if ($content->contains(".\n")) {
            return $content->before(".\n")->append('.')->trim();
        }

        return $content->trim();
    }

    /**
     * Check if the doc block has a summary.
     */
    public function hasSummary(): bool
    {
        return ! empty($this->getSummary());
    }

    /**
     * Get the description of the doc block.
     */
    public function getDescription(): ?string
    {
        if (empty($this->getTextNodes())) {
            return null;
        }

        $summary = $this->getSummary() ?? ''; // @pest-mutate-ignore Strict type assertion for PHPStan.
        
        $description = str($this->getFirstTextNode()?->text ?? '')
            ->after($summary)
            ->trim()
            ->toString();

        return empty($description) ? null : $description;
    }

    /**
     * Check if the doc block has a description.
     */
    public function hasDescription(): bool
    {
        return ! empty($this->getDescription());
    }

    /**
     * Get a Collection of the doc block's param tags.
     *
     * @return \Illuminate\Support\Collection<int, \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode>
     */
    protected function getParamTagValues(): Collection
    {
        return collect($this->phpDocNode->getParamTagValues());
    }

    /**
     * Get a param tag value by its name.
     */
    protected function getParam(string $name): ?ParamTagValueNode
    {
        $name = Str::start($name, '$');

        return $this->getParamTagValues()
            ->first(fn (ParamTagValueNode $tag) => $tag->parameterName === $name);
    }

    /**
     * Get the description of a param tag.
     */
    public function getParamDescription(string $name): ?string
    {
        return $this->getParam($name)?->description;
    }

    /**
     * Get a Collection of the doc block's var tags.
     *
     * @return \Illuminate\Support\Collection<int, \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode>
     */
    protected function getVarTagValues(): Collection
    {
        return collect($this->phpDocNode->getVarTagValues());
    }

    /**
     * Get a var tag value by its name.
     */
    protected function getVar(?string $name = null): ?VarTagValueNode
    {
        if (empty($name)) {
            return $this->getVarTagValues()->first();
        }

        $name = Str::start($name, '$');

        return $this->getVarTagValues()
            ->first(fn (VarTagValueNode $tag) => $tag->variableName === $name);
    }

    /**
     * Get the description of a var tag.
     */
    public function getVarDescription(?string $name = null): ?string
    {
        return $this->getVar($name)?->description;
    }
}
