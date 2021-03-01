<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother;
	
	use PHPUnit\Framework\TestCase;
	
	class BasicArrayTests extends TestCase
	{
		function testCopyAndPushCreatesDifferentArray()
		{
			$a = sc([ 1, 2 ]);
			$b = $a->copy();
			$b->push(3);
			
			$this->assertEquals(2, $a->count());
			$this->assertEquals(3, $b->count());
			$this->assertEquals(1, $a->first());
			$this->assertEquals(1, $b->first());
			$this->assertEquals(2, $a->last());
			$this->assertEquals(3, $b->last());
		}
		
		function testFindAndContainsGivesCorrectResults()
		{
			$a = swiss([ 3, 2, 1 ]);
			$b = swiss([ 'a' => 'b' ]);
			
			$this->assertEquals(2, $a->search(1));
			$this->assertTrue($a->contains(3));
			$this->assertFalse($a->contains(4));
			
			$this->assertTrue($b->containsKey('a'));
			$this->assertFalse($b->containsKey('b'));
			
			$this->assertTrue($b->containsKeyValue('a', 'b'));
			$this->assertFalse($b->containsKeyValue('a', 'c'));
		}
		
		function testBasicArrayManipulationWorks()
		{
			$a = sc([ 10, 20, 30 ]);
			
			$a->add(40);
			$this->assertEquals(40, $a->last());
			
			$a->push(50);
			$this->assertEquals(50, $a->last());
			
			$this->assertEquals(50, $a->pop());
			$this->assertEquals(40, $a->last());
			$this->assertEquals(10, $a->dequeue());
			$this->assertEquals(20, $a->first());
			
			$a->remove($a->search(30));
			$this->assertCount(2, $a);
		}
		
		function testArrayRemovesWorks()
		{
			$a = sc([ 0, 10, 20, 30, 40, null, 50 ]);
			
			$a = $a->withoutNulls();
			$this->assertCount(6, $a);
			$this->assertEquals(50, $a->last());
			
			$a = $a->withoutEmpty();
			$this->assertCount(5, $a);
			$this->assertEquals(10, $a->first());
		}
	}
