<?php
class Consultas extends Controller {
	function Consultas(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id('30A',1);
		define ("THISFILE",   APPPATH."controllers/compras/". $this->uri->segment(2).EXT);
	}

	function index(){
		redirect("inventario/consultas/preciosgeneral");
	}

	function preciosgeneral(){
		$this->rapyd->load('dataform','datatable');
		$cod=$this->uri->segment(4);

		$script='
		$("#codigo").focus();
			$(document).ready(function() {
				$("a").fancybox();
				$("#codigo").attr("value", "");
				$("#codigo").focus();
			});
		$("#df1").submit(function() {
				valor=$("#codigo").attr("value");
				location.href="'.site_url('inventario/consultas/preciosgeneral').'/"+valor;
				return false;
			});';

		$form = new DataForm();
		$form->script($script);

		$form->codigo = new inputField('C&oacute;digo','codigo');
		$form->codigo->size=20;
		$form->codigo->insertValue='';
		$form->codigo->append('Presente el art&iacute;culo frente al lector de c&oacute;digo de barras o escribalo directamente y luego presione ENTER');

		$form->build_form();

		$contenido = $form->output;
		if(!empty($cod)){
			$data2=$this->rprecios($cod);
			if($data2!==false){
				$contenido .=$this->load->view('view_rprecios', $data2,true);
			}else{
				$t=array();
				$t[1][1]="<b>PRODUCTO NO REGISTRADO</b>";
				$t[2][1]="";
				$t[3][1]="<b>Por Favor introduzca un C&oacute;digo de identificaci&oacute;n del Producto</b>";

				$table = new DataTable(null,$t);
				$table->cell_attributes = 'style="vertical-align:middle; text-align: center;"';
				$table->per_row  = 1;
				$table->cell_attributes = '';
				$table->cell_template = "<div style='color:red;' align='center'><#1#></div></br>";
				$table->build();
				$contenido .=$table->output;
			}
		}

		$data['content'] = $contenido;
		$data['head']    = script("jquery.js").script("plugins/jquery.fancybox.pack.js").script("plugins/jquery.easing.js").style('fancybox/jquery.fancybox.css').style("ventanas.css").style("estilos.css").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function rprecios($cod_bar=NULL){
		if(!$cod_bar)$cod_bar=$this->input->post('barras');

		$sinv= ($this->db->table_exists('sinv')) ? $this->datasis->dameval('SELECT COUNT(*) FROM sinv'): 0;
		$maes= ($this->db->table_exists('maes')) ? $this->datasis->dameval('SELECT COUNT(*) FROM maes'): 0;

		if($maes>$sinv){
			$mSQL_p='SELECT precio1,base1,precio2,precio3, barras,existen, CONCAT_WS(" ",descrip ,descrip2) AS descrip, codigo,marca,alterno,id,modelo,iva,unidad FROM maes';
			$aplica='maes';
		}else{
			$mSQL_p='SELECT precio1,base1,precio2,precio3, barras,existen, CONCAT_WS(" ",descrip ,descrip2) AS descrip, codigo,marca,alterno,id,modelo,iva,unidad,descufijo,grupo FROM sinv';
			$aplica='sinv';
		}

		$query=$this->_gconsul($mSQL_p,$cod_bar,array('codigo','barras','alterno'));
		if($query!==false){
			$row = $query->row();
			//Vemos si aplica descuento solo farmacias sinv
			if($aplica=='sinv'){
				if($row->descufijo==0){
					if($this->db->table_exists('sinvpromo')){
						$descufijo=$this->datasis->dameval('SELECT margen FROM sinvpromo WHERE codigo='.$this->db->escape($row->codigo));
						$descurazon='Descuento promocional';
						if(empty($descufijo)){
							if($this->db->field_exists('margen','grup')){
								$descufijo=$this->datasis->dameval('SELECT margen FROM grup WHERE grupo='.$this->db->escape($row->grupo));
								$descurazon='Descuento por grupo';
							}else{
								$descufijo=0;
							}
						}else{
							$descufijo=0;
						}
					}
				}else{
					$descufijo=$row->descufijo;
					$descurazon='Descuento por producto';
				}
			}

			$data['precio1']   = nformat($row->precio1);
			$data['pdescu']    = ($descufijo !=0) ? nformat($row->precio1-($row->precio1*$descufijo/100)): 0;
			$data['precio2']   = nformat($row->precio2);
			$data['precio3']   = nformat($row->precio3);
			$data['descrip']   = $row->descrip;
			$data['base1']     = nformat($row->base1);
			$data['codigo']    = $row->codigo;
			$data['alterno']   = $row->alterno;
			$data['unidad']    = $row->unidad;
			$data['descufijo'] = nformat($descufijo);
			$data['corta']     = (isset($row->corta)) ?$row->corta : '';
			$data['dvolum1']   = '';
			$data['descurazon']=(isset($descurazon)) ? $descurazon: '';
			$data['marca']     = $row->marca;
			$data['existen']   = nformat($row->existen);
			$data['barras']    = $row->barras;
			$data['modelo']    = $row->modelo;
			$data['iva']       = nformat($row->iva);
			$data['referen']   = (isset($row->referen)) ? $row->referen : 'No disponible';
			$data['iva2']      = nformat($row->base1*($row->iva/100));
			$data['moneda']    = 'Bs.F.';
			//$data['img']       = site_url('inventario/fotos/obtener/'.$row->id);
			return $data;
		}
		return false;
	}

	function sprecios($formato='CPRECIOS'){
		$data['conf']=$this->layout->settings;

		$query = $this->db->query("SELECT proteo FROM formatos WHERE nombre='$formato'");
		if ($query->num_rows() > 0){
			$row = $query->row();
			extract($data);
			ob_start();
				echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
				$_html=ob_get_contents();
			@ob_end_clean();
			echo $_html;
		}else{
			$this->load->view('view_cprecios', $data);
		}
	}

	function ssprecios($formato='CIPRECIOS',$cod_bar=NULL){
		$query = $this->db->query("SELECT proteo FROM formatos WHERE nombre='$formato'");
		if ($query->num_rows() > 0){
			$row = $query->row();
			ob_start();
				echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
				$_html=ob_get_contents();
			@ob_end_clean();
			echo $_html;
		}else{
			echo 'Formato $formato no definido';
		}
	}

	function precios(){
		$barras = array(
			'name'      => 'barras',
			'id'        => 'barras',
			'value'     => '',
			'maxlength' => '15',
			'size'      => '16',
			//'style'     => 'display:none;',
		);

		$out  = form_open('inventario/consultas/precios');
		$out .= form_label("Introduzca un Codigo ");
		$out .= form_input($barras);
		$out .= form_close();

		$link=site_url('inventario/consultas/rprecios');

		$data['script']='
		<script type="text/javascript">
		$(document).ready(function(){
			$("a").fancybox();
			$("#resp").hide();
			$("#barras").attr("value", "");
			$("#barras").focus();
			$("form").submit(function() {
				mostrar();
				return false;
			});
		});

		function mostrar(){
			$("#resp").hide();
			var url = "'.$link.'";
			$.ajax({
				type: "POST",
				url: url,
				data: $("input").serialize(),
				success: function(msg){
					$("#resp").html(msg).fadeIn("slow");
				  $("#barras").attr("value", "");
					$("#barras").focus();
				}
			});
		}
		</script>';

		$data['content'] = '<div id="resp" style=" width: 100%;" ></div>';
		$data['title']   = "<h1><center><a title='ender' href='http://192.168.0.99/proteoerp/assets/shared/images/3_b.jpg'><img src='http://192.168.0.99/proteoerp/assets/shared/images/3_s.jpg' /></a>$out</center></h1>";
		$data["head"]    = script("jquery.js").script("plugins/jquery.fancybox.pack.js").script("plugins/jquery.easing.js").style('fancybox/jquery.fancybox.css').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _gconsul($mSQL_p,$cod_bar,$busca,$suple=null){
		if(!empty($suple) AND $this->db->table_exists('suple')){
			$mSQL  ="SELECT codigo FROM suple WHERE suplemen='${cod_bar}' LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				$row = $query->row();
				$busca  =array($suple);
				$cod_bar=$row->codigo;
			}
		}

		foreach($busca AS $b){
			$mSQL  =$mSQL_p." WHERE ${b}='${cod_bar}' LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				return $query;
			}
		}

		if ($this->db->table_exists('barraspos')) {
			$mSQL  ="SELECT codigo FROM barraspos WHERE suplemen=".$this->db->escape($cod_bar)." LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				$row = $query->row();
				$cod_bar=$row->codigo;

				$mSQL  =$mSQL_p." WHERE codigo='${cod_bar}' LIMIT 1";
				$query = $this->db->query($mSQL);
				if($query->num_rows() == 0)
					 return false;
			}else{
				return false;
			}
		}else{
			return false;
		}
		return $query;
	}
}
?>
