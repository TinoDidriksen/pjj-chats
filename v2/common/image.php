<?php

function ChatImageSize($image) {
	global $handler, $pimgx, $pimgy;
	
	if (empty($image)) {
		return "width='$pimgx' height='$pimgy' border=0";
    }

	$oimg = $image;

	$crcimage = md5($image).'_'.strlen($image);
	if (empty($crcimage)) {
		return "width='$pimgx' height='$pimgy' border=0";
    }

	$query = "SELECT width,height,sum FROM chatv2.image_cache WHERE sum='{$crcimage}'";
	$result = $GLOBALS['sql']->query($query);
	if ($GLOBALS['sql']->numRows($result) > 0) {
		$img = $GLOBALS['sql']->fetchAssoc($result);
	}
	$GLOBALS['sql']->freeResult($result);

	if ($img['sum'] == $crcimage) {
		$xratio = $img['width']/$pimgx;
		$yratio = $img['height']/$pimgy;

		if (($img['width'] <= $pimgx) && ($img['height'] <= $pimgy)) {
			$rx = $img['width'];
			$ry = $img['height'];
		}
		else if ($xratio > $yratio) {
			$rx = $pimgx;
			$ry = $img['height']/$xratio;
		}
		else if ($xratio < $yratio) {
			$rx = $img['width']/$yratio;
			$ry = $pimgy;
		}
		else {
			$rx = $pimgx;
			$ry = $pimgy;
		}
		return "width='".floor($rx)."' height='".floor($ry)."' border=0";
	}
	else {
		$img = @getimagesize($image);
		
		if (!is_array($img)) {
			$img = array();
			$img['width'] = $pimgx;
			$img['height'] = $pimgy;
		}
		else {
			$img['width'] = $img[0];
			$img['height'] = $img[1];
		}

		if (($img['width'] >= 1) && ($img['height'] >= 1)) {
			$xratio = $img['width']/$pimgx;
			$yratio = $img['height']/$pimgy;

			if (($img['width'] <= $pimgx) && ($img['height'] <= $pimgy)) {
				$rx = $img['width'];
				$ry = $img['height'];
			}
			else if ($xratio > $yratio) {
				$rx = $pimgx;
				$ry = $img['height']/$xratio;
			}
			else if ($xratio < $yratio) {
				$rx = $img['width']/$yratio;
				$ry = $pimgy;
			}
			else {
				$rx = $pimgx;
				$ry = $pimgy;
			}
			$oimg = $GLOBALS['sql']->escapeString($oimg);
			$query = "INSERT INTO chatv2.image_cache
			(width,height,sum,url)
			VALUES({$img['width']},{$img['height']},'{$crcimage}','{$oimg}')";

			$GLOBALS['sql']->begin();
			if ($GLOBALS['sql']->query($query) === false) {
				$GLOBALS['sql']->rollback();
			}
			else {
				$GLOBALS['sql']->commit();
			}
			
			return "width='".floor($rx)."' height='".floor($ry)."' border=0";
		}
	}

	return "width='$pimgx' height='$pimgy' border=0";
}
