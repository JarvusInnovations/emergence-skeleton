<?php declare(strict_types=1);

final class ActiveRecordTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        require('src/TestRecord.php');
    }

    public function testFields()
    {
        $fields = TestRecord::getClassFields();
        $this->assertEquals(array_keys($fields), array('ID', 'Class', 'Created', 'CreatorID', 'Field1', 'Field2'));
    }
}