<?php


namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator;


class RawExpression
{
    /** @var string */
    private $expression;

    /**
     * RawExpression constructor.
     *
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function expression(): string
    {
        return $this->expression;
    }

    public function __toString()
    {
        return $this->expression;
    }
}