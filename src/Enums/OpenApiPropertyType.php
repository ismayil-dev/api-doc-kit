<?php

namespace IsmayilDev\ApiDocKit\Enums;

use OpenApi\Attributes\Schema;

#[Schema(type: 'string')]
enum OpenApiPropertyType: string
{
    case STRING = 'string';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case NUMBER = 'number';
    case INTEGER = 'integer';
    case BOOLEAN = 'boolean';
    case ARRAY = 'array';
    case OBJECT = 'object';
    case FILE = 'file';
    case UNDEFINED = 'undefined';

    public static function mapFromDatabaseType(string $databaseType): self
    {
        return match ($databaseType) {
            'string', 'varchar' => self::STRING,
            'integer', 'int', 'bigint' => self::INTEGER,
            'timestamp' => self::DATETIME,
            'tinyint' => self::BOOLEAN,
            'enum' => self::STRING, // TODO: Add support for enums
            default => self::UNDEFINED,
        };
    }
}
