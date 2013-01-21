<?php echo $form_scripts?>
<?php echo $form_begin?>
<?php
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$mod=true;
?>
<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table width='100%' style='font-size:11pt;background:#F2E69D;'>
	<tr>
		<td                           width='60'>Numero:</td>
		<td style='font-weight:bold;' width='70'><?php echo str_pad(trim($form->id->output),7,'0',STR_PAD_LEFT);    ?></td>
		<td                           width='60' align='right'>Fecha:</td>
		<td style='font-weight:bold;' width='90'><?php echo $form->fecha->output; ?></td>
		<td                           width='50' align='right'>Ruta:</td>
		<td style='font-weight:bold;' width='50' align='left'><?php echo $form->ruta->output;  ?></td>
		<td style='font-weight:bold;'><?php echo $this->datasis->dameval("SELECT nombre FROM lruta WHERE codigo='".$form->ruta->value."'");  ?></td>
	</tr>
</table>
<div style='border: 1px solid #9AC8DA;background: #FAFAFA'>
<table width='100%' cellspacing='0' cellpadding='0'>
	<tr style='background:#030B7A;color:#FDFDFD;font-size:10pt;'>
		<th align="center">Vaquera</th>
		<th align="center">Animal</th>
		<th align="center">Acidez</th>
		<th align="center">% Agua</th>
		<th align="center">Crioscopia</th>
		<th align="center">Grados Brix</th>
		<th align="center">% Grasa</th>
		<th align="center">Cloruros</th>
		<th align="center">Dscto.Agua</th>
	</tr>

<?php 
	for($i=0;$i<$form->max_rel_count['itlrece'];$i++) {

		$it_densidad     = "itdensidad_${i}";
		$it_lista        = "itlista_${i}";
		$it_animal       = "itanimal_${i}";
		$it_crios        = "itcrios_${i}";
		$it_h2o          = "ith2o_${i}";
		//$it_temp         = "ittemp_${i}";
		$it_brix         = "itbrix_${i}";
		$it_grasa        = "itgrasa_${i}";
		$it_acidez       = "itacidez_${i}";
		$it_cloruros     = "itcloruros_${i}";
		$it_dtoagua      = "itdtoagua_${i}";
		$it_id_lvaca     = "itid_lvaca_${i}";
		$it_id_lrece     = "itid_lrece_${i}";
		$it_id           = "itid_${i}";
		$it_lvacacodigo  = "itlvacacodigo_${i}";
		$it_lvacadescrip = "itlvacadescrip_${i}";
		$it_vaquera      = "itvaquera_${i}";
		$it_nombre       = "itnombre_${i}";
		
		echo $form->$it_lista->output.$form->$it_id->output.$form->$it_id_lvaca->output.$form->$it_nombre->output.$form->$it_vaquera->output;

?>

	<tr style='background:#E4E4E4;'>
		<td align='left'><b><?php echo $form->$it_lvacacodigo->output.' '.$form->$it_lvacadescrip->output; ?></b></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_animal->output;  ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_acidez->output;  ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_h2o->output;     ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_crios->output;   ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_brix->output;    ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_grasa->output;   ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_cloruros->output;?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_dtoagua->output; ?></td>
	</tr>
	<?php
	$mod=!$mod;
	} ?>
</table>
</div>

<?php echo $container_bl.$container_br; ?>
<?php echo $form_end; ?>
