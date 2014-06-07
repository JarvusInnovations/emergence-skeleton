<?php

namespace Migrations;

class NamespaceGroups extends AbstractMigration
{
    public static function upgrade()
    {
        // upgrade groups table
        static::addSql('ALTER TABLE `groups` CHANGE `Class` `Class` ENUM("Group","Organization","Emergence\\\\People\\\\Groups\\\\Group","Emergence\\\\People\\\\Groups\\\\Organization") NOT NULL');
        static::addSql('UPDATE `groups` SET `Class` = "Emergence\\\\People\\\\Groups\\\\Group" WHERE `Class` = "Group"');
        static::addSql('UPDATE `groups` SET `Class` = "Emergence\\\\People\\\\Groups\\\\Organization" WHERE `Class` = "Organization"');
        static::addSql('ALTER TABLE `groups` CHANGE `Class` `Class` ENUM("Emergence\\\\People\\\\Groups\\\\Group","Emergence\\\\People\\\\Groups\\\\Organization") NOT NULL');

        static::addSql('ALTER TABLE `groups` DROP `Data`');
        static::addSql('ALTER TABLE `groups` ADD `About` text NULL default NULL');

        // upgrade group_members table
        static::addSql('ALTER TABLE `group_members` CHANGE `Class` `Class` ENUM("Emergence\\\\People\\\\Groups\\\\GroupMember") NOT NULL');
        static::addSql('UPDATE `group_members` SET `Class` = "Emergence\\\\People\\\\Groups\\\\GroupMember"');
        static::addSql('ALTER TABLE `group_members` CHANGE `Role` `Role` enum("Member","Administrator","Owner","Founder") NOT NULL');
    }
}