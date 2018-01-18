<?php

namespace Emergence\Site;


abstract class RequestHandler extends \RequestHandler implements IRequestHandler
{
    protected static function sendResponse(IResponse $response)
    {
        return static::respond($response->getId(), $response->getPayload(), $response->getMode());
    }
}