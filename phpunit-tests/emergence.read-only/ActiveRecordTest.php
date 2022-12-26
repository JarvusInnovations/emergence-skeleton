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
        $this->assertEquals(array_keys($fields), array('ID', 'Class', 'Created', 'CreatorID', 'Field1', 'Field2', 'NullableDefault'), 'check class fields list');
    }

    public function testDefaults(): void
    {
        $Record = new TestRecord();
        $this->assertEquals(1, $Record->NullableDefault, 'unset value returns default');
        $this->assertNull($Record->NullableDefault = null, 'value set to null');
        $this->assertNull($Record->NullableDefault, 'value previously set explicitely to null returns null');
    }
}