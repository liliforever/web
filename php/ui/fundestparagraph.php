<?php

function FundEstTableGetColumn($bChinese)
{
    if ($bChinese)
    {
        $arColumn = array('代码', '官方'.EST_DISPLAY_CN, '官方'.PREMIUM_DISPLAY_CN, '参考'.EST_DISPLAY_CN, '参考'.PREMIUM_DISPLAY_CN, '实时'.EST_DISPLAY_CN, '实时'.PREMIUM_DISPLAY_CN);
    }
    else
    {
        $arColumn = array('Symbol', 'Official '.EST_DISPLAY_US, 'Official '.PREMIUM_DISPLAY_US, 'Fair '.EST_DISPLAY_US, 'Fair '.PREMIUM_DISPLAY_US, 'Realtime '.EST_DISPLAY_US, 'Realtime '.PREMIUM_DISPLAY_US);
    }
    return $arColumn;
}

// $ref from FundReference
function _echoFundEstTableItem($ref, $bChinese)
{
    if ($ref == false)                  return;
    if ($ref->bHasData == false)        return;
    
    $stock_ref = $ref->stock_ref;
    $strLink = GetChinaFundLink($stock_ref->sym);
    $strPrice = $stock_ref->GetPriceDisplay($ref->fPrice);
    $strPremium = $stock_ref->GetPercentageDisplay($ref->fPrice);
    $strFairPrice = $stock_ref->GetPriceDisplay($ref->fFairNetValue);
    $strFairPremium = $stock_ref->GetPercentageDisplay($ref->fFairNetValue);
    $strRealtimePrice = $stock_ref->GetPriceDisplay($ref->fRealtimeNetValue);
    $strRealtimePremium = $stock_ref->GetPercentageDisplay($ref->fRealtimeNetValue);
    
    echo <<<END
    <tr>
        <td class=c1>$strLink</td>
        <td class=c1>$strPrice</td>
        <td class=c1>$strPremium</td>
        <td class=c1>$strFairPrice</td>
        <td class=c1>$strFairPremium</td>
        <td class=c1>$strRealtimePrice</td>
        <td class=c1>$strRealtimePremium</td>
    </tr>
END;
}

function _echoFundEstTable($arRef, $bChinese)
{
	$arColumn = FundEstTableGetColumn($bChinese);
    echo <<<END
        <TABLE borderColor=#cccccc cellSpacing=0 width=560 border=1 class="text" id="estimation">
        <tr>
            <td class=c1 width=80 align=center>{$arColumn[0]}</td>
            <td class=c1 width=80 align=center>{$arColumn[1]}</td>
            <td class=c1 width=80 align=center>{$arColumn[2]}</td>
            <td class=c1 width=80 align=center>{$arColumn[3]}</td>
            <td class=c1 width=80 align=center>{$arColumn[4]}</td>
            <td class=c1 width=80 align=center>{$arColumn[5]}</td>
            <td class=c1 width=80 align=center>{$arColumn[6]}</td>
        </tr>
END;

    foreach ($arRef as $ref)
    {
        _echoFundEstTableItem($ref, $bChinese);
    }
    EchoTableEnd();
}

function _getFundRealtimeStr($fund, $strRealtimeEst, $bChinese)
{
    $future_ref = $fund->future_ref;
    $future_etf_ref = $fund->future_etf_ref;
    $etf_ref =  $fund->etf_ref;
    
    if ($future_etf_ref)
    {   // Lof and LofHk
        $strSymbol = FutureGetSinaSymbol($future_ref->GetStockSymbol());
    }
    else
    {   // GoldEtf
        $strSymbol = $fund->est_ref->GetStockSymbol();
    }
    $strHistoryLink = GetCalibrationHistoryLink($strSymbol, $bChinese);
    
    $strFutureLink = GetCommonToolLink($future_ref->GetStockSymbol(), $bChinese);
    if ($bChinese)
    {
        $str = "期货{$strRealtimeEst}{$strFutureLink}({$strHistoryLink})关联程度按照100%估算";
    }
    else
    {
        $str = "Future $strRealtimeEst assume $strFutureLink({$strHistoryLink}) 100%  related";
    }
    
    if ($future_etf_ref != $etf_ref)
    {
        $strEtfSymbol = $etf_ref->GetStockSymbol();
        if (in_arrayPairTrading($strEtfSymbol))
        {
            $strPairTradingLink = GetCommonToolLink($strEtfSymbol, $bChinese); 
        }
        else
        {
            $strPairTradingLink = $strEtfSymbol; 
        }
        
        $strFutureEtfSymbol = $future_etf_ref->GetStockSymbol();
        if ($bChinese)
        {
            $str .= ", {$strPairTradingLink}和{$strFutureEtfSymbol}关联程度按照100%估算";
        }
        else
        {
            $str .= ", assume $strPairTradingLink and $strFutureEtfSymbol 100% related";
        }
    }
    return $str.'.';    
}

function _getFundFairStr($fund, $strFairEst, $bChinese)
{
    if ($fund->index_ref && $fund->etf_ref)
    {
    	$str = $strFairEst;
    	if ($bChinese == false)	$str .= ' ';
        return $str.GetCalibrationHistoryLink($fund->index_ref->GetStockSymbol(), $bChinese).'.';
    }
    return '';
}

function _getFundParagraphStr($fund, $bChinese)
{
    $ref = $fund->stock_ref;
    $strDate = $fund->strOfficialDate;
    $strLastTime = SqlGetStockCalibrationTime($ref->strSqlId);
    $strHistoryLink = GetCalibrationHistoryLink($ref->GetStockSymbol(), $bChinese);
	$arColumn = FundEstTableGetColumn($bChinese);
	$str = $arColumn[1];
    if ($bChinese)     
    {
        $str .= '日期'.$strDate.", 校准时间($strHistoryLink)$strLastTime.";
    }
    else
    {
        $str .= ' date '.$strDate.", calibration($strHistoryLink) on $strLastTime.";
    }
    if ($fund->fFairNetValue)   $str .= ' '._getFundFairStr($fund, $arColumn[3], $bChinese);
    if ($fund->fRealtimeNetValue)   $str .= ' '._getFundRealtimeStr($fund, $arColumn[5], $bChinese);
    return $str;
}

function EchoFundArrayEstParagraph($arFund, $str, $bChinese)
{
    EchoParagraphBegin($str);
    _echoFundEstTable($arFund, $bChinese);
    EchoParagraphEnd();
}

function EchoFundEstParagraph($fund, $bChinese)
{
    $str = _getFundParagraphStr($fund, $bChinese);
    EchoFundArrayEstParagraph(array($fund), $str, $bChinese);
}

?>