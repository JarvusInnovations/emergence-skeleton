<?php

function Dwoo_Plugin_advertisement(Dwoo $dwoo, $zone, $format = '728x90')
{

	if(!$Ad = Advertisement::getNext($zone, $format))
	{
		return '[No ad available]';
	}

	return $Ad->render();
}