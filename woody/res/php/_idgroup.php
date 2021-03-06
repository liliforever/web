<?php
require_once('/php/stockgroup.php');

class GroupAcctStart extends StockAcctStart
{
    function GroupAcctStart() 
    {
        parent::StockAcctStart('groupid');
    }
    
    function EchoStockGroup()
    {
    	if ($strGroupId = $this->GetQuery())
    	{
    		$this->EchoStockGroupParagraph($strGroupId);
    	}
    	return $strGroupId;
    }
    
    function GetWhoseGroupDisplay()
    {
    	if ($strGroupId = $this->GetQuery())
    	{
    		if ($strMemberId = SqlGetStockGroupMemberId($strGroupId))
    		{
    			return $this->GetWhoseDisplay($strMemberId).SqlGetStockGroupName($strGroupId); 
    		}
    		return '';
    	}
    	return $this->GetWhoseAllDisplay();
    }
}

?>
