<?php

/* Super-simple CSS minifier adapted from */

class CssMin
{
	static public function minify($css) {
		$css = preg_replace('#\s+#', ' ', $css);
		$css = preg_replace('#/\*.*?\*/#s', '', $css);
		$css = str_replace('; ', ';', $css);
		$css = str_replace(': ', ':', $css);
		$css = str_replace(' {', '{', $css);
		$css = str_replace('{ ', '{', $css);
		$css = str_replace(', ', ',', $css);
		$css = str_replace('} ', '}', $css);
		$css = str_replace(';}', '}', $css);
		
		return trim($css);
	}
}