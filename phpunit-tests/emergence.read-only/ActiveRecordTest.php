<?php declare(strict_types=1);

final class ActiveRecordTest extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        require('src/TestRecord.php');
    }

    public function testFields(): void
    {
        $fields = TestRecord::getClassFields();
        $this->assertEquals(array_keys($fields), array('ID', 'Class', 'Created', 'CreatorID', 'Field1', 'Field2'), 'check class fields list');
    }
}