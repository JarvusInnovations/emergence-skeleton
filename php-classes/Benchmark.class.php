<?php

class Benchmark
{
    static public $livePrint = false;
	static public $startMark = null;
	static public $lastMark = null;
	static public $marks = array();
	
	static public function startLive()
	{
        header('X-Accel-Buffering: no');
		header('Content-Type: text/plain');
		ob_end_flush();
		
		static::$livePrint = true;
		static::mark('benchmark start');
	}
	
	static public function mark($label = 'mark')
	{
		$mark = microtime(true);
		static::$marks[$mark] = $label;
		
		if(!static::$startMark)
			static::$lastMark = static::$startMark = $mark;
			
		if(static::$livePrint)
		{
			printf("\n\t--%.2fs(%.2fs) - %s\n", $mark - static::$startMark, $mark - static::$lastMark, $label);
			ob_flush();
			flush();
		}
		
		static::$lastMark = $mark;
	}
}