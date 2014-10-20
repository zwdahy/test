<?php
class phpUnescape{
	function unescape($escstr) {
		preg_match_all ( "/%u[0-9A-Za-z]{4}|%.{2}|[0-9a-zA-Z.+-_]+/", $escstr, $matches );
		$ar = &$matches [0];
		$c = "";
		foreach ( $ar as $val ) {
			if (substr ( $val, 0, 1 ) != "%") {
				$c .= $val;
			} elseif (substr ( $val, 1, 1 ) != "u") {
				$x = hexdec ( substr ( $val, 1, 2 ) );
				$c .= chr ( $x );
			} else {
				$val = intval ( substr ( $val, 2 ), 16 );
				if ($val < 0x7F) // 0000-007F
{
					$c .= chr ( $val );
				} elseif ($val < 0x800) // 0080-0800
{
					$c .= chr ( 0xC0 | ($val / 64) );
					$c .= chr ( 0x80 | ($val % 64) );
				} else // 0800-FFFF
{
					$c .= chr ( 0xE0 | (($val / 64) / 64) );
					$c .= chr ( 0x80 | (($val / 64) % 64) );
					$c .= chr ( 0x80 | ($val % 64) );
				}
			}
		}
		return $c;
	}
}
?>