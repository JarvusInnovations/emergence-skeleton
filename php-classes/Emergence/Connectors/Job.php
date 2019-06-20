<?php

namespace Emergence\Connectors;

use ActiveRecord;
use Emergence\Logger;
use HandleBehavior;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Job extends ActiveRecord implements LoggerInterface
{
    use \Psr\Log\LoggerTrait;

    public $logEntries;

    // ActiveRecord configuration
    public static $tableName = 'connector_jobs';
    public static $singularNoun = 'connector job';
    public static $pluralNoun = 'connector jobs';

    // required for shared-table subclassing support
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = [__CLASS__];

    public static $fields = [
        'Title' => [
            'default' => null
        ]
        ,'Handle' => [
            'unique' => true
        ]

        ,'Status' => [
            'type' => 'enum'
            ,'values' => ['Template','Pending','InProgress','Completed','Failed','Abandoned']
            ,'default' => 'Pending'
        ]

        ,'Connector'
        ,'TemplateID' => [
            'type' => 'uint'
            ,'notnull' => false
        ]

        ,'Direction' => [
            'type' => 'enum'
            ,'values' => ['In','Out','Both']
            ,'notnull' => false
        ]

        ,'Config' => [
            'type' => 'json'
        ]
        ,'Results' => [
            'type' => 'json'
            ,'default' => null
        ]
    ];

    public static $relationships = [
        'Template' => [
            'type' => 'one-one'
            ,'class' => __CLASS__
        ]
        ,'TemplatedJobs' => [
            'type' => 'one-many'
            ,'class' => __CLASS__
            ,'foreign' => 'TemplateID'
            ,'order' => ['ID' => 'DESC']
        ]
    ];


    public function save($deep = true)
    {
        // set handle
        if (!$this->Handle) {
            $this->Handle = HandleBehavior::generateRandomHandle($this);
        }

        // call parent
        return parent::save();
    }

    public function getConnectorTitle()
    {
        $className = $this->Connector;
        return $className::getTitle();
    }

    public function logRecordDelta(ActiveRecord $Record, $options = [])
    {
        $ignoreFields = is_array($options['ignoreFields']) ? $options['ignoreFields'] : [];
        $labelRenderers = is_array($options['labelRenderers']) ? $options['labelRenderers'] : [];
        $valueRenderers = is_array($options['valueRenderers']) ? $options['valueRenderers'] : [];
        $messageRenderer = is_callable($options['messageRenderer']) ? $options['messageRenderer'] : function ($logEntry) use ($options) {
            $title = $options['title'] ?: $logEntry['record']->getTitle();
            $class = $logEntry['record']->Class;

            if (strpos($title, $class) === false) {
                $title = "$class \"$title\"";
            }

            return $logEntry['action'].' '.$title;
        };

        $logEntry = [
            'changes' => []
            ,'level' => array_key_exists('level', $options) ? $options['level'] : LogLevel::NOTICE
            ,'record' => &$Record
        ];

        foreach ($Record->originalValues as $field => $from) {
            if (in_array($field, $ignoreFields)) {
                continue;
            }

            if (is_callable($labelRenderers[$field])) {
                $fieldLabel = call_user_func($labelRenderers[$field], $logEntry, $field);
            } elseif (is_string($labelRenderers[$field])) {
                $fieldLabel = $labelRenderers[$field];
            } else {
                $fieldLabel = $field;
            }

            $to = $Record->getValue($field);

            if (is_callable($valueRenderers[$field])) {
                $from = call_user_func($valueRenderers[$field], $from, $logEntry, $field, 'from');
                $to = call_user_func($valueRenderers[$field], $to, $logEntry, $field, 'to');
            }

            $logEntry['changes'][$fieldLabel] = [
                'from' => $from
                ,'to' => $to
            ];
        }

        if ($Record->isPhantom || $Record->isNew) {
            $logEntry['action'] = 'create';
        } elseif ($Record->isDirty && count($logEntry['changes'])) {
            $logEntry['action'] = 'update';
        } else {
            return;
        }

        $logEntry['message'] = call_user_func($messageRenderer, $logEntry);

        $this->log(
            $logEntry['level'],
            $logEntry['message'],
            [
                'changes' => $logEntry['changes'],
                'record' => $Record
            ]
        );

        return $logEntry;
    }

    public function logInvalidRecord(\ActiveRecord $Record, $title = null)
    {
        return $this->log(
            LogLevel::WARNING,
            'Invalid {recordClass} record: {recordTitle}',
            [
                'validationErrors' => $Record->validationErrors,
                'recordClass' => get_class($Record),
                'recordTitle' => $title ?: $Record->getTitle()
            ]
        );
    }

    public function logException(\Exception $e)
    {
        return $this->log(
            LogLevel::ERROR,
            'Exception({exceptionClass}): {exceptionMessage}',
            [
                'exception' => $e,
                'exceptionClass' => get_class($e),
                'exceptionMessage' => $e->getMessage()
            ]
        );
    }

    public function getLogPath()
    {
        return $this->isPhantom ? null : \Site::$rootPath.'/site-data/connector-jobs/'.$this->ID.'.json';
    }

    public function writeLog($compress = true)
    {
        $logPath = $this->getLogPath();

        if (!$logPath) { // record is phantom
            return;
        }

        $logDirectory = dirname($logPath);
        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }

        file_put_contents($logPath, json_encode($this->logEntries));
        if ($compress === true) {
            exec("bzip2 $logPath");
        }
    }

    public function log($level, $message, array $context = [])
    {
        $entry = [
            'time' => date('Y-m-d H:i:s'),
            'message' => $message,
            'context' => $context,
            'level' => $level
        ];

        $this->logEntries[] = $entry;

        return $entry;
    }
}
