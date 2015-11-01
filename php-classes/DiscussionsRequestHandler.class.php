<?php



 class DiscussionsRequestHandler extends RequestHandler
 {
     public static function handleRequest()
     {
         // handle JSON requests
        if (static::peekPath() == 'json') {
            static::$responseMode = static::shiftPath();
        }

        // route request
        switch ($discussionHandle = static::shiftPath()) {
            case '':
            case false:
            {
                return static::handleBrowseRequest();
            }

            default:
            {
                // lookup discussion by handle
                if (!$Discussion = Discussion::getByHandle($discussionHandle)) {
                    return static::throwNotFoundError();
                }

                return static::handleDiscussionRequest($Discussion);
            }
        }
     }

     public static function handleBrowseRequest()
     {
         // execute search and return response
        return static::respond('discussions', array(
            'data' => Discussion::getAll(array(
                'order' => array('ID' => 'DESC')
            ))
        ));
     }


     public static function handleDiscussionRequest(Discussion $Discussion)
     {
         switch ($action = static::shiftPath()) {
            case 'comment':
            {
                return CommentsRequestHandler::handleCreateRequest($Discussion);
            }

            case '':
            case false:
            {
                return static::respond('discussion', array(
                    'data' => $Discussion
                ));
            }

            default:
            {
                return static::throwNotFoundError();
            }
        }
     }
 }