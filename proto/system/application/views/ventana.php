<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Proteo ERP</title>
<link rel="stylesheet" href="<?=base_url()?>assets/default/css/rapyd.css" type="text/css" media="all" />
<?=$rapyd_head?>
</head>
<body>
<div class='encabe'>
<img src="/proteoerp/assets/default/css/templete_01.jpg" width="120" >
</div>
<div id="content">
	<div>Proteo ERP version 0.1  </div>
	<div class="line"></div>	
	<div class="left"><?=$lista ?></div>
	<div class="right">
		<?=$content?>
		<div class="line"></div>
		<div class="code"><?=$code?></div>
	</div>
	<div class="line"></div>
	<div class="footer">
	<p>Tiempo de la consulta {elapsed_time} seg | Proteo ERP | <a href="http://www.codeigniter.com">Code Igniter Home</a> | <a href="http://www.rapyd.com">Rapyd Library Home</a></p>
	</div>
    </div>
</body>
</html>