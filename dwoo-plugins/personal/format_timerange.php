<?php

function Dwoo_Plugin_format_timerange(Dwoo $dwoo, $from, $to, $seperator='&ndash;')
{
	$from = getdate($from);
	$to = getdate($to);
	
	$from['m'] = $from['hours']<=12?'am':'pm';
	$to['m'] = $to['hours']<=12?'am':'pm';

	$from['hours'] %= 12;
	$to['hours'] %= 12;
	
	if($from['year'] != $to['year'])
	{
		// diff year
		return 'yearspan';
	}
	elseif($from['mon'] != $to['mon'])
	{
		// same year, diff month
		return 'monthspan';
	}
	elseif($from['mday'] != $to['mday'])
	{
		// same month, diff day
		return 'dayspan';
	}
	elseif($from['m'] != $to['m'])
	{
		// same day, diff am/pm
		return sprintf('%u:%02u%s%s%u:%02u%s', $from['hours'], $from['minutes'], $from['m'], $seperator, $to['hours'], $to['minutes'], $to['m']);
	}
	else//if($from['hours'] != $to['hours'])
	{
		// same am/pm
		return sprintf('%u:%02u%s%u:%02u%s', $from['hours'], $from['minutes'], $seperator, $to['hours'], $to['minutes'], $to['m']);
	}
	
}


?>