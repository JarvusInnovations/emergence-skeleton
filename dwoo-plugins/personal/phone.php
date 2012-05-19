<?php

function Dwoo_Plugin_phone(Dwoo $dwoo, $input, $format = '(%s) %s-%s')
{
	return Format::usPhone($input, $format);
}
