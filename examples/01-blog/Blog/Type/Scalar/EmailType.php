<?php

declare(strict_types=1);

namespace GraphQL\Examples\Blog\Type\Scalar;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Utils\Utils;
use UnexpectedValueException;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

class EmailType extends CustomScalarType
{
    public function __construct(array $config = [])
    {
        parent::__construct([
            'serialize' => [self::class, 's_serialize'],
            'parseValue' => [self::class, 's_parseValue'],
            'parseLiteral' => [self::class, 's_parseLiteral'],
        ]);
    }

    /**
     * Serializes an internal value to include in a response.
     */
    public static function s_serialize(string $value): string
    {
        // Assuming internal representation of email is always correct:
        return $value;

        // If it might be incorrect and you want to make sure that only correct values are included in response -
        // use following line instead:
        // return $this->parseValue($value);
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function s_parseValue($value)
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new UnexpectedValueException('Cannot represent value as email: ' . Utils::printSafe($value));
        }

        return $value;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input
     *
     * @throws Error
     */
    public static function s_parseLiteral(Node $valueNode): string
    {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        if (! filter_var($valueNode->value, FILTER_VALIDATE_EMAIL)) {
            throw new Error('Not a valid email', [$valueNode]);
        }

        return $valueNode->value;
    }
}
