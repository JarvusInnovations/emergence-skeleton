<?php

namespace Emergence\Comments;

use ActiveRecord;
use HandleBehavior;

class Comment extends \VersionedRecord
{
    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = [__CLASS__];

    // ActiveRecord configuration
    public static $tableName = 'comments';
    public static $singularNoun = 'comment';
    public static $pluralNoun = 'comments';
    public static $collectionRoute = '/comments';

    public static $fields = [
        'ContextClass',
        'ContextID' => 'uint',
        'Handle' => [
            'unique' => true
        ],
        'ReplyToID' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'Message' => [
            'type' => 'clob',
            'fulltext' => true
        ]
    ];

    public static $relationships = [
        'Context' => [
            'type' => 'context-parent'
        ],
        'ReplyTo' => [
            'type' => 'one-one',
            'class' => __CLASS__
        ]
    ];

    public static $validators = [
        'Context' => [
            'validator' => 'require-relationship',
            'required' => true
        ],
        'Message' => [
            'validator' => 'string_multiline',
            'errorMessage' => 'You must provide a message.'
        ]
    ];

    public static $searchConditions = [
        'Message' => [
            'qualifiers' => ['any', 'message']
        ]
    ];

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        // implement handles
        HandleBehavior::onValidate($this, $this->_validator);

        // save results
        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        // set handle
        if (!$this->Handle) {
            $this->Handle = HandleBehavior::generateRandomHandle(__CLASS__, 12);
        }

        parent::save();
    }

    /**
     * Differentially apply a complete array of new comments data to a given context
     */
    public static function applyCommentsData(ActiveRecord $Context, array $commentsData)
    {
        // index existing comment records by ID
        $existingComments = [];

        foreach ($Context->Comments as $Comment) {
            $existingComments[$Comment->ID] = $Comment;
        }


        // create new and update existing comment
        $comments = [];
        foreach ($commentsData as $commentData) {
            if (empty($commentData['Message'])) {
                throw new Exception('Comment data must have Message set');
            }

            if (
                !empty($commentData['ID'])
                && ($Comment = $existingComments[$commentData['ID']])
            ) {
                $Comment->Message = $commentData['Message'];
            } else {
                $Comment = static::create([
                    'Message' => $commentData['Message']
                ]);
            }

            $comments[] = $Comment;
        }


        // write new list to relationship
        $Context->Comments = array_merge($existingComments, $comments);
    }
}
