<?php

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use Attribute;
use OpenApi\Attributes\Response;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SuccessResponse extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 200,
            description: 'Successful response',
        );
    }
}
