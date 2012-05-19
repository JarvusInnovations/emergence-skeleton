<?php

function Dwoo_Plugin_markdown(Dwoo $dwoo, $text)
{
	$Markdown = new MarkdownExtra_Parser();
	return $Markdown->transform(strip_tags($text));
}