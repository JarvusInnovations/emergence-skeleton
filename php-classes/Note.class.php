<?php



 class Note extends ActiveRecord
 {
     // ActiveRecord configuration
    public static $tableName = 'notes';

     public static $fields = array(
        'Body' => array(
            'type' => 'clob'
        )
    );
 }