<?php
	/** @noinspection PhpUnhandledExceptionInspection */
	
	namespace YetAnother;
	
	use PHPUnit\Framework\TestCase;
	
	class ChunkTests extends TestCase
	{
		function testBasicUnkeyedChunkingWorks()
		{
			$chunked = sc(range(1, 50))->chunk(10);
			
			$this->assertEquals(5, $chunked->count());
			$chunked->each(fn(array $chunk) => $this->assertCount(10, $chunk));
		}
		
		function testBasicUnkeyedOddCountChunkingWorks()
		{
			$chunked = sc(range(1, 25))->chunk(10);
			
			$this->assertEquals(3, $chunked->count());
			$this->assertCount(10, $chunked[0]);
			$this->assertCount(10, $chunked[1]);
			$this->assertCount(5, $chunked[2]);
		}
		
		function testBasicOddCountKeyedChunkingWorks()
		{
			$chunked = sc(range(1, 9))->chunk(3);
			
			$this->assertEquals(3, $chunked->count());
			$i = 0;
			$chunked->each(function(array $chunk) use($i)
			{
				$this->assertCount(3, $chunk);
				foreach($chunk as $key=>$value)
					$this->assertEquals($i++, $key);
			});
		}
		
		function testPredicateChunkingWorks()
		{
			$chunked = sc(range(1, 10))->chunkIf(fn(int $k, int $i) => $i == 8 || $k == 9);
			
			$this->assertEquals(3, $chunked->count());
			$this->assertCount(7, $chunked[0]);
			$this->assertCount(2, $chunked[1]);
			$this->assertEquals(8, $chunked[1][0]);
			$this->assertCount(1, $chunked[2]);
			$this->assertEquals(10, $chunked[2][0]);
		}
	}
