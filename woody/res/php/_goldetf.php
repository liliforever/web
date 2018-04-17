<?php
require_once('_stock.php');
require_once('_fundgroup.php');

class _GoldEtfGroup extends _StockGroup
{
    // constructor 
    function _GoldEtfGroup($strSymbol) 
    {
        StockPrefetchData(array($strSymbol));
        GetChinaMoney();

        $this->cny_ref = new CnyReference('USCNY');
        $this->ref = new GoldFundReference($strSymbol);
        
        parent::_StockGroup(array($this->ref->stock_ref));
    }
} 

function _echoAdminTestParagraph($group, $bChinese)
{
    $str = _GetEtfAdjustString($group->ref->stock_ref, $group->ref->est_ref, $bChinese);
    EchoParagraph($str);
}

function EchoAll($bChinese)
{
    global $group;
    $fund = $group->ref;
    
    EchoFundEstParagraph($fund, $bChinese);
    EchoReferenceParagraph(array($fund->est_ref, $fund->future_ref, $group->cny_ref, $fund->stock_ref), $bChinese);
    EchoFundTradingParagraph($fund, false, $bChinese);    
    EchoFundHistoryParagraph($fund, $bChinese);

    if ($group->strGroupId) 
    {
        _EchoTransactionParagraph($group, $bChinese);
	}
    
    EchoPromotionHead('goldetf', $bChinese);
    if (AcctIsAdmin())
    {
        _echoAdminTestParagraph($group, $bChinese);
    }
}

function EchoMetaDescription($bChinese)
{
    global $group;

    $fund = $group->ref;
    $strDescription = _GetStockDisplay($fund->stock_ref);
    $strEst = _GetStockDisplay($fund->est_ref);
    $strFuture = _GetStockDisplay($fund->future_ref);
    $strCNY = _GetStockDisplay($group->cny_ref);
    if ($bChinese)  $str = '根据'.$strEst.', '.$strFuture.'和'.$strCNY.'等因素计算'.$strDescription.'净值的网页工具.';
    else             $str = 'Web tool to estimate the net value of '.$strDescription.' based on '.$strEst.' and '.$strFuture.'.';
    EchoMetaDescriptionText($str);
}

    AcctNoAuth();
    $group = new _GoldEtfGroup(StockGetSymbolByUrl());

?>
