<?php

// skip if people table not generated yet
if (!DB::oneRecord('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "tags"')) {
    print("Skipping migration because table doesn't exist yet\n");
    return static::STATUS_SKIPPED;
}

// skip if Class column already exists
if (DB::oneValue('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "tags" AND COLUMN_NAME = "Class"')) {
    print("Skipping migration because table already has Class column\n");
    return static::STATUS_SKIPPED;
}

// upgrade tags table
print("Upgrading tags table\n");
DB::nonQuery('ALTER TABLE `tags` ADD `Class` ENUM("Tag") NOT NULL AFTER `ID`;');


return static::STATUS_EXECUTED;