<?php require_once('php/_calibrationhistory.php'); ?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title><?php EchoTitle(); ?></title>
<meta name="description" content="<?php EchoUrlSymbol(); ?>校准历史记录页面. 用于查看, 比较和调试估算的股票价格或者基金净值之间的校准情况. 最新的校准时间一般会直接显示在该股票或者基金的页面.">
<link href="../../common/style.css" rel="stylesheet" type="text/css" />
</head>

<body bgproperties=fixed leftmargin=0 topmargin=0>
<?php _LayoutTopLeft(); ?>

<div>
<h1><?php EchoTitle(); ?></h1>
<?php EchoCalibrationHistory(); ?>
<p>相关软件:
<?php EchoStockGroupLinks(); ?>
</p>
</div>

<?php LayoutTailLogin(); ?>

</body>
</html>
