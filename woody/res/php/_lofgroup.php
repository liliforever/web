<?php
require_once('_fundgroup.php');
require_once('/php/ui/smaparagraph.php');

class _LofGroup extends _MyStockGroup
{
    var $etf_his;
    var $cny_ref;
    
    // constructor 
    function _LofGroup() 
    {
        $etf_ref = $this->ref->etf_ref; 
        if ($etf_ref)
        {
            $this->etf_his = new StockHistory($etf_ref);
            parent::_MyStockGroup(array($this->ref->stock_ref, $etf_ref));
        }
        else
        {
            $this->etf_his = false;
            parent::_MyStockGroup(array($this->ref->stock_ref));
        }
    } 
    
    function ConvertToEtfTransaction($etf_convert_trans, $lof_trans)
    {
        $fund = $this->ref;
        $etf_convert_trans->AddTransaction($fund->EstEtfQuantity($lof_trans->iTotalShares), $lof_trans->fTotalCost / $fund->fCNY);
    }
    
    function ConvertToLofTransaction($lof_convert_trans, $etf_trans)
    {
        $fund = $this->ref;
        $lof_convert_trans->AddTransaction($fund->EstLofQuantity($etf_trans->iTotalShares), $etf_trans->fTotalCost * $fund->fCNY);
    }
    
    function EchoArbitrageParagraph($bChinese)
    {
        $lof_trans = $this->GetStockTransactionCN();
        $etf_trans = $this->GetStockTransactionUS();
        $this->OnArbitrage();
        
        $lof_convert_trans = new MyStockTransaction($this->ref->stock_ref, $this->strGroupId);
        $lof_convert_trans->AddTransaction($lof_trans->iTotalShares, $lof_trans->fTotalCost);
        $this->ConvertToLofTransaction($lof_convert_trans, $etf_trans);
        
        $etf_convert_trans = new MyStockTransaction($this->ref->etf_ref, $this->strGroupId);
        $etf_convert_trans->AddTransaction($etf_trans->iTotalShares, $etf_trans->fTotalCost);
        $this->ConvertToEtfTransaction($etf_convert_trans, $lof_trans);
    
        EchoParagraphBegin($bChinese ? '策略分析' : 'Arbitrage analysis');
        _EchoArbitrageTableBegin($bChinese);
        $sym = $this->arbi_trans->ref->sym;
        if ($sym->IsSymbolA())
        {
            $arbi_convert_trans = new MyStockTransaction($this->ref->etf_ref, $this->strGroupId);
            $this->ConvertToEtfTransaction($arbi_convert_trans, $this->arbi_trans);
            _EchoArbitrageTableItem2($this->arbi_trans, $lof_convert_trans); 
            _EchoArbitrageTableItem2($arbi_convert_trans, $etf_convert_trans); 
        }
        else
        {
            $arbi_convert_trans = new MyStockTransaction($this->ref->stock_ref, $this->strGroupId);
            $this->ConvertToLofTransaction($arbi_convert_trans, $this->arbi_trans);
            _EchoArbitrageTableItem2($arbi_convert_trans, $lof_convert_trans); 
            _EchoArbitrageTableItem2($this->arbi_trans, $etf_convert_trans); 
        }
        EchoTableEnd();
        EchoParagraphEnd();
    }

    function _getAdjustString($bChinese)
    {
    	$ref = $this->ref;
        $est_ref = $ref->est_ref;
/*        $stock_ref = $this->ref->stock_ref;
        $strSymbol = $stock_ref->GetStockSymbol();
        $strQuery = sprintf('%s=%.4f&%s=%.2f&CNY=%.4f', $strSymbol, $stock_ref->fPrevPrice, $est_ref->GetStockSymbol(), $est_ref->fPrevPrice, $this->cny_ref->fPrevPrice);*/
        $strSymbol = $ref->GetStockSymbol();
        $strDate = $ref->strDate;
        $strCNY = SqlGetForexCloseString($ref->strForexSqlId, $strDate);
        if ($history = SqlGetStockHistoryByDate($est_ref->GetStockId(), $strDate))
        {
        	$strEst = $history['close'];
        }
        else
        {
        	$strEst = $est_ref->strPrevPrice;
        }
        $strQuery = sprintf('%s=%s&%s=%s&CNY=%s', $strSymbol, $ref->strPrice, $est_ref->GetStockSymbol(), $strEst, $strCNY);
        return _GetAdjustLink($strSymbol, $strQuery, $bChinese);
    }

    function EchoAdminTestParagraph($bChinese)
    {
        if (AcctIsAdmin() == false)     return;
        
        $fund = $this->ref;
        $str = $this->GetDebugString($bChinese);
        if ($fund->etf_ref)
        {
            $str .= HTML_NEW_LINE._GetStockHistoryDebugString(array($this->etf_his), $bChinese);
        }
        $str .=  HTML_NEW_LINE.$this->_getAdjustString($bChinese);
        if ($fund->index_ref && $fund->etf_ref)
        {
            $str .=  HTML_NEW_LINE._GetEtfAdjustString($fund->index_ref, $fund->etf_ref, $bChinese);
        }
        EchoParagraph($str);
    }
} 

function EchoEtfSymbol()
{
    global $group;
    if ($group->ref->etf_ref)
    {
        echo $group->ref->etf_ref->GetStockSymbol();
    }
}

function EchoMetaDescription($bChinese)
{
    global $group;
    
    $fund = $group->ref;
    $strDescription = _GetStockDisplay($fund->stock_ref);
    $strBase = $group->cny_ref->strDescription;
    if ($fund->index_ref)   $strBase .= '/'.$fund->index_ref->strDescription;
    if ($fund->etf_ref)     $strBase .= '/'.$fund->etf_ref->strDescription;
    
    if ($bChinese)  $str = '根据'.$strBase.'等因素计算'.$strDescription.'实时净值的网页工具, 提供不同市场下统一的交易记录和转换持仓盈亏等功能.';
    else              $str = 'Net value of '.$strDescription.' based on '.$strBase.'.';
    EchoMetaDescriptionText($str);
}

function EtfEstLof($fEtf, $lof_ref)
{
    global $group;
    if ($fEtf)		return $group->ref->_estLofByEtf($fEtf, $group->ref->fCNY);
    return $lof_ref->GetStockSymbol();
}

?>
