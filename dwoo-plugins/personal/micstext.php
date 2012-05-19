<?php

function Dwoo_Plugin_micstext(Dwoo $dwoo, $text, $mode = 'format')
{
	return Format::micsText($text, $mode);
}