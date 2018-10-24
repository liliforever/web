<?php
require_once('smaparagraph.php');

function _callbackEtfSma($ref, $fEst = false)
{
	if ($fEst === false)
	{
		return $ref;
	}
	return $ref->EstFromPair($fEst);
}

function EchoEtfArraySmaParagraph($ref, $arEtfRef, $bChinese, $callback2 = false)
{
	foreach ($arEtfRef as $etf_ref)
	{
		EchoSmaParagraph($ref, $bChinese, '', $etf_ref, _callbackEtfSma, $callback2);
	}
}

function EchoEtfSmaParagraph($ref, $bChinese, $str = false, $callback2 = false)
{
	EchoSmaParagraph($ref->pair_ref, $bChinese, $str, $ref, _callbackEtfSma, $callback2);
}

?>
