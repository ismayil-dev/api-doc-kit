<?php

namespace IsmayilDev\ApiDocKit\Attributes\Enums;

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
}
