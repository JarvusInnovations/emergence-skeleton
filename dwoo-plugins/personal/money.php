<?php

function Dwoo_Plugin_money(Dwoo $dwoo, $value, $format = '%n', $avoidDecimal = false)
{
	if($avoidDecimal)
	{
		return '$'.($value + 0);
	}
	else
	{
		// strip non-digits
		$value = preg_replace('/[^\d.]/','',$value);
	
		return money_format($format, $value ? $value : 0);
	}
}
