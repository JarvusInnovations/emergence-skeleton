<?php

namespace Migrations;

class GroupLeftUnique extends AbstractMigration
{
    static public function upgrade()
    {
        static::addSql('ALTER TABLE groups ADD UNIQUE `Left` (`Left`)');
    }
}