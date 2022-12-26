<?php

class TestRecord extends ActiveRecord
{
    public static $fields = array(
        'Field1',
        'Field2',
        'NullableDefault' => array(
            'type' => 'int',
            'notnull' => false,
            'default' => 1
        )
    );
}