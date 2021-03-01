<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother;
	
	use PHPUnit\Framework\TestCase;
	
	class AssociativeArrayTests extends TestCase
	{
		function testAssociativeArrayManipulationWorks()
		{
			$a = sc([ 'a' => 'A', 'b' => 'B' ]);
			
			$a->unsetKeys([ 'a' ]);
			$this->assertCount(1, $a);
			$this->assertEquals('B', $a['b']);
			
			$a->overwrite([
				'b' => 'C',
				'c' => 'D'
			]);
			
			$this->assertCount(2, $a);
			$this->assertEquals('C', $a['b']);
			$this->assertEquals('D', $a['c']);
		}
	}
