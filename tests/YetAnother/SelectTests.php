<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother;
	
	use Exception;
	use PHPUnit\Framework\TestCase;
	use stdClass;
	
	class SelectTests extends TestCase
	{
		function testSelectOnValidMixedCollectionWorks()
		{
			$collection = $this->getSelectTestCollection();
			
			$collection->select([ 'name', 'tag' ])
				->each(function($value)
				{
					$this->assertIsArray($value);
					$this->assertArrayNotHasKey('id', $value);
					$this->assertNotEmpty($value['name']);
					$this->assertNotEmpty($value['tag']);
				});
			
			$collection->select([ 'name', 'tag' ], true)
				->eachKeyed(function($key, $value)
				{
					$this->assertTrue(in_array($key, [ 'a', 'o' ]));
					$this->assertIsArray($value);
					$this->assertArrayNotHasKey('id', $value);
					$this->assertNotEmpty($value['name']);
					$this->assertNotEmpty($value['tag']);
				});
		}
		
		function testSelectOnInvalidMixedCollectionThrows()
		{
			$this->expectException(Exception::class);
			
			$collection = $this->getSelectTestCollection();
			$collection->push(true);
			
			$collection->select([ 'name', 'tag' ]);
		}
		
		/**
		 * @return SwissCollection
		 */
		private function getSelectTestCollection(): SwissCollection
		{
			$arr = ['id' => 2, 'name' => 'Hello', 'tag' => 12345];
			$obj = new stdClass();
			$obj->id = 3;
			$obj->name = 'Goodbye';
			$obj->tag = 54321;
			
			return sc(['a' => $arr, 'o' => $obj]);
		}
	}
