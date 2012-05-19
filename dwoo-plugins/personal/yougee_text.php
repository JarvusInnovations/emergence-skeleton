<?php

function Dwoo_Plugin_yougee_text(Dwoo $dwoo, $text)
{
	return Yougee_Formatter::formatText($text);
}