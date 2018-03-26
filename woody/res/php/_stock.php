<?php
require_once('_resstock.php');
require_once('/php/mystock.php');
require_once('/php/ui/stocktable.php');
require_once('_editformcommon.php');
require_once('_edittransactionform.php');
require_once('_stocklink.php');

class _MyStockGroup extends MyStockGroup
{
    var $ref;                
    var $arDisplayRef = false;      // For stock information display and debug
    
    var $strName;            //  Group name
    
    // constructor 
    function _MyStockGroup($arRef) 
    {
        $this->strName = $arRef[0]->GetStockSymbol();
        $strMemberId = AcctIsLogin();
        if ($strMemberId == false)     return;
        
        $strGroupName = $this->strName;
        if (($strGroupId = SqlGetStockGroupId($strGroupName, $strMemberId)) === false)
        {
            SqlInsertStockGroup($strMemberId, $strGroupName);
            $strGroupId = SqlGetStockGroupId($strGroupName, $strMemberId);
            if ($strGroupId)
            {
                foreach ($arRef as $ref)
                {
                    if ($ref->sym->IsIndex() == false)
                    {
                        SqlInsertStockGroupItem($strGroupId, $ref->strSqlId);
                    }
                }      
            }
        }
        parent::MyStockGroup($strGroupId, $arRef);
    }
    
    function GetDebugString($bChinese)
    {
        $strFile = $bChinese ? '数据' : 'Data';
        foreach ($this->arDisplayRef as $ref)
        {
            if ($ref)
            {
                $strFile .= ' '.DebugGetFileLink($ref->strFileName);
            }
        }
        return $strFile;
    }
}

function _GetStockHistoryDebugString($ar_his, $bChinese)
{
    $strHistory = $bChinese ? '历史' : 'History';   
    $strSma = $bChinese ? SMA_DISPLAY_CN : SMA_DISPLAY_US;
    foreach ($ar_his as $his)
    {
        if ($his)
        {
            $strHistory .= ' '.$his->DebugLink();
            $strSma .= ' '.$his->DebugConfigLink();
        }
    }
    return $strHistory.HTML_NEW_LINE.$strSma;
}

// ****************************** LOF/ETF/INDEX(FUTURE) convert functions *******************************************************

function EstLofByIndex($fIndex, $fFactor, $fCNY)
{
    return $fIndex * $fCNY / $fFactor;
}

function EstEtfByIndex($fIndex, $fEtfFactor)
{
//    return $fIndex / $fEtfFactor;
    return EstLofByIndex($fIndex, $fEtfFactor, 1.0);
}

// ****************************** Transaction table *******************************************************

function _echoTransactionTableItem($trans, $strSymbol, $transaction, $bReadOnly, $bChinese)
{
    $strDate = GetSqlTransactionDate($transaction);
    $strPrice = $trans->ref->GetPriceDisplay($transaction['price']);
    $strFees = round_display_str($transaction['fees']);
    if ($bReadOnly)
    {
        $strEditDelete = '';
    }
    else
    {
        $strEditDelete = StockGetEditDeleteTransactionLink($transaction['id'], $bChinese);
    }
    
    echo <<<END
    <tr>
        <td class=c1>$strDate</td>
        <td class=c1>$strSymbol</td>
        <td class=c1>{$transaction['quantity']}</td>
        <td class=c1>$strPrice</td>
        <td class=c1>$strFees</td>
        <td class=c1>{$transaction['remark']}</td>
        <td class=c1>$strEditDelete</td>
    </tr>
END;
}

function _echoTransactionTableData($group, $iStart, $iNum, $bChinese)
{
    $arSymbols = array();
    $bReadOnly = IsStockGroupReadOnly($group->strGroupId);
    if ($result = SqlGetStockTransactionByGroupId($group->strGroupId, $iStart, $iNum)) 
    {
        while ($transaction = mysql_fetch_assoc($result)) 
        {
            $trans = $group->GetStockTransactionByStockGroupItemId($transaction['groupitem_id']);
            if ($trans)
            {
                $strSymbol = $trans->GetStockSymbol();
                if (in_array($strSymbol, $arSymbols))    $strSymbolLink = $strSymbol;
                else
                {
                    $strSymbolLink = GetMyStockLink($strSymbol, $bChinese);
                    $arSymbols[] = $strSymbol;
                }
                _echoTransactionTableItem($trans, $strSymbolLink, $transaction, $bReadOnly, $bChinese);
            }
        }
        @mysql_free_result($result);
    }
}

define ('MAX_TRANSACTION_DISPLAY', 10);
function _EchoTransactionTable($group, $iStart, $iNum, $bChinese)
{
    if ($bChinese)     
    {
        $arColumn = array('日期', '股票代码', '数量', PRICE_DISPLAY_CN, '交易费用', '备注', '操作');
    }
    else
    {
        $arColumn = array('Date', 'Symbol', 'Quantity', PRICE_DISPLAY_US, 'Fees', 'Remark', 'Operation');
    }
    
    echo <<<END
    <TABLE borderColor=#cccccc cellSpacing=0 width=640 border=1 class="text" id="average">
    <tr>
        <td class=c1 width=100 align=center>{$arColumn[0]}</td>
        <td class=c1 width=80 align=center>{$arColumn[1]}</td>
        <td class=c1 width=70 align=center>{$arColumn[2]}</td>
        <td class=c1 width=80 align=center>{$arColumn[3]}</td>
        <td class=c1 width=60 align=center>{$arColumn[4]}</td>
        <td class=c1 width=170 align=center>{$arColumn[5]}</td>
        <td class=c1 width=80 align=center>{$arColumn[6]}</td>
    </tr>
END;

    _echoTransactionTableData($group, $iStart, $iNum, $bChinese);
    EchoTableEnd();
}

// ****************************** Arbitrage table *******************************************************

function _EchoArbitrageTableBegin($bChinese)
{
    if ($bChinese)     
    {
        $arColumn = array('股票代码', '对冲数量', '对冲价格', '折算数量', '折算价格', '折算净值盈亏');
    }
    else
    {
        $arColumn = array('Symbol', 'Quantity', 'Price', 'Convert Total', 'Convert Avg', 'Convert Profit');
    }
    
    echo <<<END
    <TABLE borderColor=#cccccc cellSpacing=0 width=510 border=1 class="text" id="arbitrage">
    <tr>
        <td class=c1 width=80 align=center>{$arColumn[0]}</td>
        <td class=c1 width=90 align=center>{$arColumn[1]}</td>
        <td class=c1 width=70 align=center>{$arColumn[2]}</td>
        <td class=c1 width=100 align=center>{$arColumn[3]}</td>
        <td class=c1 width=70 align=center>{$arColumn[4]}</td>
        <td class=c1 width=100 align=center>{$arColumn[5]}</td>
    </tr>
END;
}

function _EchoArbitrageTableItem2($arbi_trans, $convert_trans)
{
    _EchoArbitrageTableItem($arbi_trans->iTotalShares, $arbi_trans->GetAvgCostDisplay(), $convert_trans);
}

function _EchoArbitrageTableItem($iQuantity, $strPrice, $trans)
{
    $strSymbol = $trans->GetStockSymbol();
    $strQuantity = strval($iQuantity); 
    $strConvertTotal = strval($trans->iTotalShares); 
    $strConvertPrice = $trans->GetAvgCostDisplay();
    $strConvertProfit = $trans->GetProfitDisplay();
    
    echo <<<END
    <tr>
        <td class=c1>$strSymbol</td>
        <td class=c1>$strQuantity</td>
        <td class=c1>$strPrice</td>
        <td class=c1>$strConvertTotal</td>
        <td class=c1>$strConvertPrice</td>
        <td class=c1>$strConvertProfit</td>
    </tr>
END;
}

// ****************************** Portfolio table *******************************************************

function _EchoPortfolioTableBegin($bChinese)
{
    if ($bChinese)     
    {
        $arColumn = array('股票', '总数量', '平均价格', '百分比', '持仓', '盈亏', '货币');
    }
    else
    {
        $arColumn = array('Stock', 'Total', 'Avg', 'Percentage', 'Amount', 'Profit', 'Money');
    }
    
    echo <<<END
        <TABLE borderColor=#cccccc cellSpacing=0 width=640 border=1 class="text" id="portfolio">
        <tr>
            <td class=c1 width=100 align=center>{$arColumn[0]}</td>
            <td class=c1 width=90 align=center>{$arColumn[1]}</td>
            <td class=c1 width=90 align=center>{$arColumn[2]}</td>
            <td class=c1 width=100 align=center>{$arColumn[3]}</td>
            <td class=c1 width=120 align=center>{$arColumn[4]}</td>
            <td class=c1 width=90 align=center>{$arColumn[5]}</td>
            <td class=c1 width=50 align=center>{$arColumn[6]}</td>
        </tr>
END;
}

function _EchoPortfolioItem($strGroupId, $trans, $bChinese)
{
    $ref = $trans->ref;
    $sym = $ref->sym;
    
    if ($sym->IsSymbolA())           $strMoney = '';
    else if ($sym->IsSymbolH())     $strMoney = $bChinese ? '港币$' : 'HK$';
    else                              $strMoney = '$';
    
    $strTransactions = StockGetSingleTransactionLink($strGroupId, $sym->strSymbol, $bChinese);
    if ($trans->iTotalShares == 0)
    {
        $strAvgCost = '';
        $strPercentage = '';
        $strAmount = '';
    }
    else
    {
        $strAvgCost = $trans->GetAvgCostDisplay();
        $strPercentage = $ref->GetPercentageDisplay($trans->GetAvgCost());
        $strAmount = $trans->GetValueDisplay();
    }
    $strTotalShares = strval($trans->iTotalShares); 
    $strProfit = $trans->GetProfitDisplay();
    
    echo <<<END
    <tr>
        <td class=c1>$strTransactions</td>
        <td class=c1>$strTotalShares</td>
        <td class=c1>$strAvgCost</td>
        <td class=c1>$strPercentage</td>
        <td class=c1>$strAmount</td>
        <td class=c1>$strProfit</td>
        <td class=c1>$strMoney</td>
    </tr>
END;
}

function _echoGroupPortfolioParagraph($group, $bChinese)
{
    if ($group->GetTotalRecords() > 0)
	{
		EchoParagraphBegin('');
	    _EchoPortfolioTableBegin($bChinese);    
        foreach ($group->arStockTransaction as $trans)
        {
            if ($trans->iTotalRecords > 0)
            {
                _EchoPortfolioItem($group->strGroupId, $trans, $bChinese);
            }
		}
		EchoTableEnd();    
		EchoParagraphEnd();
	}
}

// ****************************** Money table *******************************************************

function _EchoMoneyTableBegin($bChinese)
{
    $strGroupLink = StockGetGroupLink($bChinese);
    if ($bChinese)     
    {
        $arColumn = array($strGroupLink, '持仓', '盈亏', '全部持仓', '全部盈亏', '货币');
    }
    else
    {
        $arColumn = array($strGroupLink, 'Value', 'Profit', 'All Value', 'All Profit', 'Money');
    }
    
    echo <<<END
        <TABLE borderColor=#cccccc cellSpacing=0 width=640 border=1 class="text" id="money">
        <tr>
            <td class=c1 width=110 align=center>{$arColumn[0]}</td>
            <td class=c1 width=100 align=center>{$arColumn[1]}</td>
            <td class=c1 width=100 align=center>{$arColumn[2]}</td>
            <td class=c1 width=140 align=center>{$arColumn[3]}</td>
            <td class=c1 width=140 align=center>{$arColumn[4]}</td>
            <td class=c1 width=50 align=center>{$arColumn[5]}</td>
        </tr>
END;
}

function _echoMoneyItem($strGroup, $strMoney, $fValue, $fProfit, $fConvertValue, $fConvertProfit)
{
    $strValue = GetNumberDisplay($fValue);
    $strProfit = GetNumberDisplay($fProfit);
    $strConvertValue = GetNumberDisplay($fConvertValue);
    $strConvertProfit = GetNumberDisplay($fConvertProfit);
    
    echo <<<END
    <tr>
        <td class=c1>$strGroup</td>
        <td class=c1>$strValue</td>
        <td class=c1>$strProfit</td>
        <td class=c1>$strConvertValue</td>
        <td class=c1>$strConvertProfit</td>
        <td class=c1>$strMoney</td>
    </tr>
END;
}

function _EchoMoneyGroupData($group, $strLink, $fUSDCNY, $fHKDCNY)
{
    $group->ConvertCurrency($fUSDCNY, $fHKDCNY);
    _echoMoneyItem($strLink, '', $group->multi_amount->fCNY, $group->multi_profit->fCNY, $group->multi_amount->fConvertCNY, $group->multi_profit->fConvertCNY);
    if (FloatNotZero($group->multi_amount->fUSD) || FloatNotZero($group->multi_profit->fUSD))
        _echoMoneyItem('', '$', $group->multi_amount->fUSD, $group->multi_profit->fUSD, $group->multi_amount->fConvertUSD, $group->multi_profit->fConvertUSD);
    if (FloatNotZero($group->multi_amount->fHKD) || FloatNotZero($group->multi_profit->fHKD))
        _echoMoneyItem('', 'HK$', $group->multi_amount->fHKD, $group->multi_profit->fHKD, $group->multi_amount->fConvertHKD, $group->multi_profit->fConvertHKD);
}


// ****************************** Premotion Headline *******************************************************

function _echoQQgroupPromotion()
{
    echo <<<END
        <p>请扫下面的二维码或者点击最右边的链接加入Woody创建的QQ群204836363
        <a target="_blank" href="http://shang.qq.com/wpa/qunwpa?idkey=2eb90427cf5fc1c14f4ebd8f72351d4a09e259cf48f137e312cd54163bd5c165"><img border="0" src="http://pub.idqqimg.com/wpa/images/group.png" alt="Alcoholic Anonymus" title="Alcoholic Anonymus"></a>
        <br /><img src=/woody/image/qq.png alt="QQ group 204836363 scan QR code" />
END;
}

function _echoWeixinPromotion()
{
    echo <<<END
        <p>请扫下面的二维码关注Palmmicro<a href="/woody/blog/palmmicro/20161014cn.php">微信公众订阅号</a>sz162411. 
        <br /><img src=/woody/blog/photo/20161014_qrcode_mid.jpg alt="Palmmicro wechat public account sz162411 middle size QR code" />
END;
}

function _getGuangFaLink()
{
    $strHttp = "http://clickeggs.gf.com.cn/qrcode/page/index.html?channel=normal&branch_no=1507&bn_alterornot=1&recommend_no=3046963&rn_alterornot=1&product_kind=normal&product_no=&bank_type=&fund_nos=&from_source_info=qrcode_user_3046963_x";
    return DebugGetExternalLink($strHttp, '广发开户');
}

function _echoMyPromotion()
{
    $strExampleLink = _getGuangFaLink();
    echo <<<END
        <p>随机显示广告位招租, 显示一张图片和一个外部链接如{$strExampleLink}.
           提供<a href="/account/visitorcn.php">网站访问</a>和广告展示统计, 广告费用按同期<a href="/woody/blog/entertainment/20110509cn.php">Google Adsense</a>收益标准收取.
        <br />觉得这个页面有用? 可以打赏支持一下. 
        <br /><img src=/woody/blog/photo/wxpay_small.jpg alt="Small QRcode to pay 1 RMB to Woody in Weixin" />
END;
}

function _getDevGuideLink($strVer, $bChinese)
{
    if ($strVer != '')
    {
        $strQuery = '#'.$strVer;
    }
    else
    {
        $strQuery = false;
    }
    return UrlBuildPhpLink('/woody/blog/entertainment/20150818', $strQuery, '开发记录', 'Development Record', $bChinese);
}

function EchoPromotionHead($strVer, $bChinese)
{
    if ($bChinese)  echo '<h3>讨论和建议</h3>';
    else              echo '<h3>Discussions and Suggestions</h3>';
    
    if ($bChinese)
    {
        $iVal = rand(1, 3);
        if ($iVal == 1)          _echoQQgroupPromotion();
        else if ($iVal == 2)    _echoWeixinPromotion();
        else if ($iVal == 3)    _echoMyPromotion();
        EchoNewLine();
    }
    echo _getDevGuideLink($strVer, $bChinese);
    EchoParagraphEnd();
    
    if (AcctIsAdmin())
    {
        $str = DebugGetDebugFileLink();
        EchoParagraph($str);
    }
}

// ****************************** Money Paragraph *******************************************************

function EchoMoneyParagraph($group, $fUSDCNY, $fHKDCNY, $bChinese)
{
    if ($bChinese)     
    {                                          
        $str = '折算货币';
    }
    else
    {
        $str = 'Convert currency';
    }
    EchoParagraphBegin($str);
    _EchoMoneyTableBegin($bChinese);
    _EchoMoneyGroupData($group, $group->strName, $fUSDCNY, $fHKDCNY);
    EchoTableEnd();
    EchoParagraphEnd();
}

// ****************************** Transaction Paragraph *******************************************************

function _EchoTransactionParagraph($group, $bChinese)
{
    $strGroupId = $group->strGroupId;
    
    if ($group->GetTotalRecords() > 0)
    {
        if ($bChinese)     
        {                                          
            $str = '交易记录';
        }
        else
        {
            $str = 'Stock transaction record';
        }
        $str .= ' '.StockGetAllTransactionLink($strGroupId, false, $bChinese);
        EchoParagraphBegin($str);
        _EchoTransactionTable($group, 0, MAX_TRANSACTION_DISPLAY, $bChinese);
        EchoParagraphEnd();
    }
    
    StockEditTransactionForm($strGroupId, false, $bChinese);
    _echoGroupPortfolioParagraph($group, $bChinese);
}

// ****************************** String functions *******************************************************

function EchoUrlSymbol()
{
    if ($strSymbol = UrlGetQueryValue('symbol'))  
    {
        echo $strSymbol;
    }
}

function _GetWhoseDisplay($strOwnerMemberId, $strMemberId, $bChinese)
{
    if ($strOwnerMemberId == $strMemberId)
    {
        if ($bChinese)  $str = '我的';
        else             $str = 'My ';
    }
    else
    {
	    $str = AcctGetMemberDisplay($strOwnerMemberId);
        if ($bChinese)  $str .= '的';
        else             $str .= "'s ";
    }
    return $str;
}

function _GetWhoseStockGroupDisplay($strMemberId, $strGroupId, $bChinese)
{
    $strGroupMemberId = SqlGetStockGroupMemberId($strGroupId);
    $str = _GetWhoseDisplay($strGroupMemberId, $strMemberId, $bChinese); 
    return $str.SqlGetStockGroupName($strGroupId);
}

function _GetAllDisplay($str, $bChinese)
{
    if ($str)   return $str;
    
    if ($bChinese)  $str = '全部';
    else             $str = 'All';
    return $str;
}

function _GetStockDisplay($ref)
{
    return $ref->strDescription.'('.$ref->GetStockSymbol().')';
}

function sortStockReferenceBySymbol($arRef)
{
    $ar = array();
    foreach ($arRef as $ref)
    {
        $strSymbol = $ref->GetStockSymbol();
        $ar[$strSymbol] = $ref; 
    }
    ksort($ar);
    
    $arSort = array();
    foreach ($ar as $str => $ref)
    {
        $arSort[] = $ref;
    }
    return $arSort;
}


?>
