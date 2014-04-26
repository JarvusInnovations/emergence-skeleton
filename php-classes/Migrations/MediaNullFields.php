<?php

namespace Migrations;

class MediaNullFields extends AbstractMigration
{
    static public function upgrade()
    {
        static::addSql('ALTER TABLE `media` CHANGE `Width` `Width` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `Height` `Height` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `Duration` `Duration` FLOAT UNSIGNED NULL DEFAULT NULL, CHANGE `Caption` `Caption` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');
    }
}