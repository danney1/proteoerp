<?php
if(count($parametros)==0) show_error('Faltan parametros ');
$id   = $parametros[0];
$dbid = $this->db->escape($id);
$mSQL_1 = $this->db->query('SELECT
	a.fecha,a.numero,b.nomfis,a.proveed,a.nombre,a.tipo_doc,a.reten,a.nombre,a.flete AS breten,
	b.direc1,b.direc2,b.direc3,b.telefono,b.rif, a.fecha AS ffactura,a.nfiscal
FROM scst AS a
JOIN sprv AS b ON a.proveed=b.proveed
WHERE a.reten>0 AND a.id='.$dbid);
if($mSQL_1->num_rows()==0) show_error('Registro no encontrado');
$row = $mSQL_1->row();

$fecha    = dbdate_to_human($row->fecha);
$ffecha   = dbdate_to_human($row->ffactura);

$numero   = trim($row->numero);
$proveed  = $this->us_ascii2html($row->proveed);
$tipo_doc = trim($row->tipo_doc);
$breten   = $row->breten;
$reten    = $row->reten;
$nombre   = (empty($row->nomfis))? $this->us_ascii2html($row->nombre) : $this->us_ascii2html($row->nomfis);
$direc1   = $this->us_ascii2html($row->direc1);
$direc2   = $this->us_ascii2html($row->direc2);
$direc3   = $this->us_ascii2html($row->direc3);
$telefono = htmlspecialchars(trim($row->telefono));
$rif      = htmlspecialchars(trim($row->rif));
$nfiscal  = htmlspecialchars(trim($row->nfiscal));
$tari1    = 3;

$mSQL_2 = $this->db->query('SELECT
e.codigorete,c.activida,e.base,e.porcen,e.monto
FROM gereten AS e
JOIN rete AS c ON c.codigo=e.codigorete
WHERE e.origen=\'SCST\' AND e.idd='.$dbid);

if(!empty($nfiscal)){
	$titcontrol = '<b>Nro. CONTROL:</b> '.$nfiscal;
}else{
	$titcontrol = '';
}
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?php echo $this->config->item('charset'); ?>" >
<title>DATOS DEL CONTRIBUYENTE SUJETO A RETENCION DE I.S.L.R. <?php echo $numero ?></title>
<link rel="STYLESHEET" href="<?php echo $this->_direccion ?>/assets/default/css/formatos.css" type="text/css" >
</head>
<body>
<script type="text/php">
if ( isset($pdf) ) {
	$font = Font_Metrics::get_font("verdana");;
	$size = 6;
	$color = array(0,0,0);
	$text_height = Font_Metrics::get_font_height($font, $size);

	$foot = $pdf->open_object();

	$w = $pdf->get_width();
	$h = $pdf->get_height();

	// Draw a line along the bottom
	$y = $h - $text_height - 24;
	$pdf->line(16, $y, $w - 16, $y, $color, 0.5);

	$pdf->close_object();
	$pdf->add_object($foot, "all");

	$text = "PP {PAGE_NUM} de {PAGE_COUNT}";

	// Center the text
	$width = Font_Metrics::get_text_width("PP 1 de 2", $font, $size);
	$pdf->page_text($w / 2 - $width / 2, $y, $text, $font, $size, $color);
}
</script>
<div id="body">
	<table style="width: 90%;" align='center'>
	<thead>
		<tr>
		<td>
			<?php $this->incluir('X_CINTILLO'); ?>
			<br><br>
			<table style="width: 80%;font-size:8pt; " align='center' class="header">
			<tr>
				<td style='text-align:justify;' colspan='2'>
				Ley I.S.L.R.-Art 9: En concordancia con lo establecido en el Art 1 del reglamento estan obligados a
				practicar la retenci&oacute;n del impuesto, los deudores o pagadores de enriquecimientos netos o ingresos brutos
				de las siguientes actividades realizadas en el pais por personas naturales residentes, personas naturales no residentes
				personas jur&iacute;dicas domiciliadas y personas jur&iacute;dicas no domiciliadas y asimilada, a estas de acuerdo con los siguientes porcentajes
				</td>
			</tr>
			</table>
			<br><br>
			<div class="page" style="font-size: 7pt">
			<table style="width: 100%;" class="header">
			<tr>
				<td><h1 style="text-align: left;font-size:11pt;">DATOS DEL CONTRIBUYENTE SUJETO A RETENCION DE I.S.L.R. :</h1></td>
				<td><h1 style="text-align: right">N&uacute;mero: <?php echo $numero ?></h1></td>
			</tr>
			</table>
			<table style="width: 100%; font-size: 8pt;">
				<tr><td>Raz&oacute;n Social:</td><td><b><?php echo $proveed." ".$nombre; ?></b></td></tr>
				<tr><td>Direcci&oacute;n:</td><td><b><?php echo $direc1." ".$direc2." ".$direc3; ?></b></td></tr>
				<tr><td>Tel&eacute;fono:</td><td><b><?php echo $telefono; ?></b>R.I.F. :<b><?php echo $rif; ?></b></td></tr>
			</table>
			</div>
	</thead>
	</table>
	<div class="page" style="font-size: 8pt">
	<table style="width: 90%;" align='center' class="header">
		<tr style="border-bottom:1px solid;">
			<td><div align="left" style="font-size: 11pt;"><b>DATOS DE LA RETENCI&Oacute;N:</b></div></td>
		</tr>
	</table>

	<table style="width: 80%;" class="header" align='center' -->
		<tr>
			<td><div align="left"  style="font-size: 8pt"><b>DOCUMENTO:</b></div></td>
			<td><div align="rigth" style="font-size: 8pt"><?php echo $tipo_doc.$numero.' '.$titcontrol;  ?></div></td>
		 </tr><tr>
			<td><div align="left"  style="font-size: 8pt"><b>FECHA DE EMISION.:</b></div></td>
			<td><div align="rigth" style="font-size: 8pt"><?php echo $ffecha ?></div></td>
		</tr>
		 </tr><tr>
			<td><div align="left"  style="font-size: 8pt"><b>FECHA DE RECEPCION:</b></div></td>
			<td><div align="rigth" style="font-size: 8pt"><?php echo $fecha ?></div></td>
		</tr>

		<?php if($mSQL_2->num_rows()==0){ ?>
			<tr style='color: #111111;background: #EEEEEE;'>
				<td><div align="left"  style="font-size: 8pt"><b>CONCEPTO:</b></div></td>
				<td><div align="rigth" style="font-size: 8pt">GASTOS DE TRANSPORTE POR FLETES</div></td>
			</tr><tr>
				<td><div align="left"  style="font-size: 8pt"><b>MONTO DEL PAGO OBJETO DE RETENCI&Oacute;N  Bs. :</b></div></td>
				<td><div align="rigth" style="font-size: 8pt"><?php echo nformat($breten)?></div></td>
			</tr><tr>
				<td><div align="left"  style="font-size: 8pt"><b>MONTO DE LA BASE IMPONIBLE Bs. :</b></div></td>
				<td><div align="rigth" style="font-size: 8pt"><?php echo nformat($breten) ?></div></td>
			</tr><tr>
				<td><div align="left" style="font-size: 8pt"><b>PORCENTAJE DE RETENCI&Oacute;N:</b></div></td>
				<td><div align="rigth" style="font-size: 8pt"><?php echo nformat($tari1) ?> &#37; </div></td>
			</tr>
		<?php }else{
			$reten=0;
			$detalle = $mSQL_2->result();
			foreach ($detalle AS $items){
				$reten+=$items->monto;
		?>
			<tr style='color: #111111;background: #EEEEEE;'>
				<td><div align="left"  style="font-size: 8pt"><b>CONCEPTO:</b></div></td>
				<td><div align="rigth" style="font-size: 8pt"><?php echo htmlspecialchars($items->activida) ?></div></td>
			</tr><tr>
				<td><div align="left"  style="font-size: 8pt"><b>MONTO DEL PAGO OBJETO DE RETENCI&Oacute;N  Bs. :</b></div></td>
				<td><div align="rigth" style="font-size: 8pt"><?php echo nformat($items->monto)?></div></td>
			</tr><tr>
				<td><div align="left"  style="font-size: 8pt"><b>MONTO DE LA BASE IMPONIBLE Bs. :</b></div></td>
				<td><div align="rigth" style="font-size: 8pt"><?php echo nformat($items->base) ?></div></td>
			</tr><tr>
				<td><div align="left" style="font-size: 8pt"><b>PORCENTAJE DE RETENCI&Oacute;N:</b></div></td>
				<td><div align="rigth" style="font-size: 8pt"><?php echo nformat($items->porcen) ?> &#37; </div></td>
			</tr>

		<?php }
		}
		?>
	</table>
	</div>
	<br>
	<table style="width: 60%;" class="header" align='center'>
		<tr style='border-top: 1px solid;background:#AAAAAA;'>
			<td><div align="left"  style="font-size: 11pt"><b>TOTAL MONTO DE LA RETENCI&Oacute;N:</b></div></td>
			<td><div align="rigth" style="font-size: 11pt">Bs.<?php echo nformat($reten) ?></div></td>
		</tr>
	</table>
	<p style='height:20px'> </p>

	<div style='height:70px;width: 100%;align:center;'>
	<p style='height: 50px;'> </p>
	<table style="width:90%;border-top:1px solid;"  class="header" align='center' >
		<tr>
			<td><b><div align="center" style="font-size: 8pt"><b>Agente de Retenci&oacute;n:</b></div></td>
			<td><b><div align="center" style="font-size: 8pt"><b>Firma y Sello:</b></div></td>
		</tr>
	</table>
	</div>
</div>
 </body>
</html>