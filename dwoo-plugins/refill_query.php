<?php

function Dwoo_Plugin_refill_query(Dwoo_Core $dwoo, array $rest=array())
{
	$data = $_GET;
	unset($data['path']);
	return http_build_query(array_merge($data, $rest));
}