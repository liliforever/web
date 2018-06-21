<?php require_once('php/_mystockgroup.php'); ?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Chinese ETF Net Value Tools</title>
<meta name="description" content="Each of these tools estimates the net value of one Chinese ETF and makes available arbitrage analysis.">
<link href="../../common/style.css" rel="stylesheet" type="text/css" />
</head>

<body bgproperties=fixed leftmargin=0 topmargin=0>
<?php _LayoutTopLeft(false); ?>

<div>
<h1>Chinese ETF Net Value Tools</h1>
<p>Each of these tools estimates the net value of one Chinese ETF and makes available arbitrage analysis.
</p>
<?php MyStockGroupEchoAll(false); ?>
<p>Related software:
<?php 
    EchoStockCategoryLinks(false);
    EchoStockGroupLinks(false);
?>
</p>
</div>

<?php LayoutTailLogin(false); ?>

</body>
</html>