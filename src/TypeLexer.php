<?php declare(strict_types=1);

namespace phpDocumentor\Reflection;

use Doctrine\Common\Lexer\AbstractLexer;

final class TypeLexer extends AbstractLexer
{
     protected $primitives = [
        'string',
        'integer',
        'boolean',
        'float',
        'object',
        'resource',
        'scalar',
        'callable',
        'self',
    ];

     protected $pseudoTypes = [
        'int',
        'bool',
        'mixed',
        'false',
        'true',
        'class-string',
        'callback',
        '$this',
        'static',
        'parent',
        'real',
        'double',
    ];

     protected $collections = [
        'array',
        'iterable',
    ];

    public const T_NONE = 1;

    public const T_COMPOUND_OPERATOR = 2;
    public const T_INTERSECTION_OPERATOR = 3;
    public const T_NULLABLE_OPERATOR  = 4;
    public const T_GREATER_THAN = 5;
    public const T_LESS_THAN = 6;
    public const T_COMMA = 7;
    public const T_CLOSE_PARENTHESIS = 8;
    public const T_OPEN_PARENTHESIS  = 9;
    public const T_CLOSE_SQUARE_BRACKET = 10;
    public const T_OPEN_SQUARE_BRACKET  = 11;

    public const T_QUALIFIED_NAME = 100;
    public const T_FULLY_QUALIFIED_NAME = 101;
    public const T_PRIMITIVE_TYPE = 102;
    public const T_COLLECTION_TYPE = 103;
    public const T_PSEUDO_TYPE = 104;

    public const T_NULL = 200;
    public const T_VOID = 201;

    public function __construct()
    {
        // Performance optimalisation, isset is vastly superior to in_array
        $this->primitives = array_flip($this->primitives);
        $this->collections = array_flip($this->collections);
        $this->pseudoTypes = array_flip($this->pseudoTypes);
    }

    /**
     * @inheritDoc
     */
    protected function getCatchablePatterns()
    {
        return [
            '\$this', // only keyword allowed with a special character
            '[a-zA-Z_\\x80-\\xff\\\][a-zA-Z0-9_\\x80-\\xff]*(?:\\\[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*)*', // keyword or QSEN
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getNonCatchablePatterns()
    {
        return [
            '\s+',
            '(.)'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getType(&$value)
    {
        if (isset($this->primitives[$value])) {
            return static::T_PRIMITIVE_TYPE;
        }
        if (isset($this->collections[$value])) {
            return static::T_COLLECTION_TYPE;
        }
        if ($value === 'void') {
            return static::T_VOID;
        }
        if (strtolower($value) === 'null') {
            return static::T_NULL;
        }
        if (isset($this->pseudoTypes[$value])) {
            return static::T_PSEUDO_TYPE;
        }

        if  (ctype_alpha($value[0]) || $value[0] === '\\') {
            if (strpos($value, '\\') === 0) {
                return self::T_FULLY_QUALIFIED_NAME;
            }

            if (strpos($value, '\\') !== false) {
                return self::T_QUALIFIED_NAME;
            }

            return self::T_PSEUDO_TYPE;
        }

        // Recognize symbols
        switch ($value) {
            case '|':
                return self::T_COMPOUND_OPERATOR;
            case '&':
                return self::T_INTERSECTION_OPERATOR;
            case ',':
                return self::T_COMMA;
            case '?':
                return self::T_NULLABLE_OPERATOR;
            case '<':
                return self::T_LESS_THAN;
            case '>':
                return self::T_GREATER_THAN;
            case '(':
                return self::T_OPEN_PARENTHESIS;
            case ')':
                return self::T_CLOSE_PARENTHESIS;
            case '[':
                return self::T_OPEN_SQUARE_BRACKET;
            case ']':
                return self::T_CLOSE_SQUARE_BRACKET;

            // Default
            default:
                return self::T_NONE;
        }
    }
}
