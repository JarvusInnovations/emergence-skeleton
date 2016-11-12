<?php
    namespace Emergence\Developer\Tools\Git;

use Site,Exception,Git,Benchmark,Emergence\Git\Repo;

class RequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        switch ($action ? $action : $action = static::shiftPath()) {
            case '':
                return static::handleHomeRequest();

            default:
                return RepoRequestHandler::handleRequest($action);
        }

        return static::throwNotFoundError();
    }

    public static function respond($responseID, $responseData = [], $responseMode = false)
    {
        \Emergence\Developer\Tools\RequestHandler::respond($responseID, array_merge($responseData,[

        ]),$responseMode);
    }

    public static function handleHomeRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        return static::respond('home', [
            'Repos' => Repo::getAll()
        ]);
    }
}