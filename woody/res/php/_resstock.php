<?php
require_once('/php/layout.php');
require_once('/woody/php/_navwoody.php');

function GetMenuArray($bChinese)
{
    if ($bChinese)
    {
        return array('adr' => 'ADR工具',
                      'goldetf' => '黄金ETF',
                      'gradedfund' => '分级基金',
                      'lof' => 'LOF工具',
                      'lofhk' => '香港LOF',
                     );
    }
    else
    {
         return array('adr' => 'ADR Tools',
                      'goldetf' => 'Gold ETF',
                      'gradedfund' => 'Graded Fund',
                      'lof' => 'LOF Tools',
                      'lofhk' => 'HK LOF',
                     );
    }
}

function _menuItemClass($iLevel, $strClass, $bChinese)
{
    $iLevel --;
    $ar = GetMenuArray($bChinese);
    $strDisp = $ar[$strClass];
   	NavWriteItemLink($iLevel, $strClass, UrlGetPhp($bChinese), $strDisp);
    NavContinueNewLine();
}

function ResMenu($arLoop, $bChinese)
{
    $iLevel = 1;
    
	NavBegin();
	WoodyMenuItem($iLevel, 'res', $bChinese);
	NavContinueNewLine();
	if ($arLoop[0] == 'ach')
	{
	    _menuItemClass($iLevel, 'adr', $bChinese);
	}
	else if ($arLoop[0] == 'sh501018')
	{
	    _menuItemClass($iLevel, 'lof', $bChinese);
	}
	else if ($arLoop[0] == 'sh501021')
	{
	    _menuItemClass($iLevel, 'lofhk', $bChinese);
	}
	else if ($arLoop[0] == 'sh518800')
	{
	    _menuItemClass($iLevel, 'goldetf', $bChinese);
	}
	else if ($arLoop[0] == 'sh502004')
	{
	    _menuItemClass($iLevel, 'gradedfund', $bChinese);
	}
    NavDirLoop($arLoop);
	NavContinueNewLine();
    NavSwitchLanguage($bChinese);
    NavEnd();
}

function NavStockSoftware($bChinese)
{
    $iLevel = 1;
    
	NavBegin();
	WoodyMenuItem($iLevel, 'res', $bChinese);
	NavContinueNewLine();
    NavMenuSet(GetMenuArray($bChinese));
	NavContinueNewLine();
    NavSwitchLanguage($bChinese);
    NavEnd();
}

function _LayoutTopLeft($bChinese = true)
{
    LayoutTopLeft(NavStockSoftware, $bChinese);
}

function NavLoopGradedFund($bChinese)
{
    ResMenu(GradedFundGetSymbolArray(), $bChinese);
}

function _LayoutGradedFundTopLeft($bChinese = true)
{
    LayoutTopLeft(NavLoopGradedFund, $bChinese);
}

function NavLoopGoldEtf($bChinese)
{
    ResMenu(GoldEtfGetSymbolArray(), $bChinese);
}

function _LayoutGoldEtfTopLeft($bChinese = true)
{
    LayoutTopLeft(NavLoopGoldEtf, $bChinese);
}

function NavLoopLof($bChinese)
{
    ResMenu(LofGetSymbolArray(), $bChinese);
}

function _LayoutLofTopLeft($bChinese = true)
{
    LayoutTopLeft(NavLoopLof, $bChinese);
}

function NavLoopLofHk($bChinese)
{
    ResMenu(LofHkGetSymbolArray(), $bChinese);
}

function _LayoutLofHkTopLeft($bChinese = true)
{
    LayoutTopLeft(NavLoopLofHk, $bChinese);
}

function NavLoopAdr($bChinese)
{
    ResMenu(AdrGetSymbolArray(), $bChinese);
}

function _LayoutAdrTopLeft($bChinese = true)
{
    LayoutTopLeft(NavLoopAdr, $bChinese);
}

?>
