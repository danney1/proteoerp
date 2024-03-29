<?php
class sfacdespfyco extends Controller {

	function sfacdespfyco()
	{
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(111,1);    
		define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
	}
	function index(){
		redirect("ventas/sfacdespfyco/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->load->library('encrypt');
		$this->load->helper('form');


		$atts = array(
		      'width'      => '800',
		      'height'     => '600',
		      'scrollbars' => 'yes',
		      'status'     => 'yes',
		      'resizable'  => 'yes',
		      'screenx'    => '0',
		      'screeny'    => '0'
		    );
		    
		$filter = new DataFilter("Filtro");
		$select=array("a.tipo_doc as tipoa","IF(a.tipo_doc='F','Activa',IF(a.tipo_doc='D','Devolucion',IF(a.tipo_doc='X','Anulada','Otro'))) AS tipo_doc", 
		"a.cod_cli as cliente","a.fecha","if(a.referen='C','Cred','Cont') referen","a.numero","a.nombre","a.totalg as total","a.vd","d.nombre as vendedor");
		$select[]="GROUP_CONCAT(e.despacha) LIKE '%S%' AS parcial";
		$filter->db->select($select);
		$filter->db->from('sfac AS a');
		$filter->db->join('snot AS c' ,'a.numero=c.factura','LEFT');
		$filter->db->join('vend AS d' ,'a.vd=d.vendedor');
		$filter->db->join('sitems AS e','e.numa=a.numero AND e.tipoa=a.tipo_doc'); 
		$filter->db->groupby('a.numero,a.tipo_doc');
		$filter->db->where('a.fdespacha IS NULL');
		$filter->db->where('a.tipo_doc','F');
		$filter->db->where('c.factura IS NULL');
		$filter->db->orderby("a.fecha DESC, a.numero");
		$filter->db->_escape_char='';           
		$filter->db->_protect_identifiers=false;
		
		$filter->fechad = new dateonlyField("Desde", "fechad");
		$filter->fechah = new dateonlyField("Hasta", "fechah");
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";

		$filter->numero = new inputField("N&uacute;mero", "a.numero");
		$filter->numero->size = 20;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = "ventas/cajeros/dataedit/show/<#cajero#>";

		if(!$this->rapyd->uri->is_set("search")) $filter->db->where('a.fecha','CURDATE()');

		function descheck($numero){
			$data = array(
			  'name'    => 'despacha[]',
			  'id'      => $numero,
			  'value'   => $numero,
			  'checked' => FALSE);
			return form_checkbox($data);
		}
		
		$seltodos='Seleccionar <a id="todos" href=# >Todos</a> <a id="nada" href=# >Ninguno</a> <a id="alter" href=# >Invertir</a>';

		$grid = new DataGrid("$seltodos");
		$grid->use_function('descheck');
		$grid->use_function('colum');
		$grid->use_function('parcial');
		
		function colum($tipo_doc) {
			if ($tipo_doc=='Anulada')
				return ('<b style="color:red;">'.$tipo_doc.'</b>');
			else
				return ($tipo_doc);
		}
		
		function parcial($parcial) {
			if ($parcial)
				return '*';
			else
				return '';
		}
		
    $comprobante=anchor("formatos/ver/SNOTEXP/<#tipoa#>/<#numero#>",'Imprimir');
		$link=anchor_popup('ventas/sfacdespfyco/detalle/<#numero#>/<#tipoa#>','<#numero#>',$atts);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>");
		$grid->column("Tipo","referen");
		$grid->column("N&uacute;mero",'<parcial><#parcial#></parcial>'.$link);
		$grid->column("Cliente","cliente");
		$grid->column("Nombre","nombre");
		$grid->column("Total","<number_format><#total#>|2</number_format>","align=right");
		$grid->column("Vendedor","vd","align=center");
		$grid->column("Status","<colum><#tipo_doc#></colum>");
		$grid->column("Despachado","<descheck><#numero#></descheck>","align=center"); 
		$grid->column("Nota de Entrega",$comprobante); 
		
		$grid->build();
		//echo $grid->db->last_query();

		$script ='<script type="text/javascript">
		$(document).ready(function() {
			$("#todos").click(function() { $("#adespacha").checkCheckboxes();   });
			$("#nada").click(function()  { $("#adespacha").unCheckCheckboxes(); });
			$("#alter").click(function() { $("#adespacha").toggleCheckboxes();  });
		});
		</script>';
		$consulta =$grid->db->last_query();
		$mSQL = $this->encrypt->encode($consulta);
    
		$campo="<form action='/../../proteoerp/xlsauto/repoauto2/'; method='post'>
 		<input size='100' type='hidden' name='mSQL' value='$mSQL'>
 		<input type='submit' value='Descargar a Excel' name='boton'/>
 		</form>";

		$attributes = array('id' => 'adespacha');
		$data['content'] =  $filter->output.$campo;
		if($grid->recordCount>0)
		$data['content'] .=form_open('ventas/sfacdespfyco/procesar',$attributes).$grid->output.form_submit('mysubmit', 'Aceptar').form_close().$script;
		$data['title']   =  "<h1>Despacho Express</h1>";
		$data["head"]    =  script("jquery-1.2.6.pack.js");
		$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}
	function procesar(){
		//print_r($_POST);
		foreach($_POST['despacha'] as $fila){
			$usuario = $this->session->userdata('usuario');
			$mSQL="UPDATE sitems SET despacha='S', fdespacha=CURDATE(), udespacha='$usuario' WHERE numa='$fila' AND tipoa='F' ";
			$this->db->simple_query($mSQL);
			$mSQL="UPDATE sfac SET fdespacha=CURDATE(), udespacha='$usuario' WHERE numero='$fila' AND tipo_doc='F' ";
			$this->db->simple_query($mSQL);
		}
		redirect("ventas/sfacdespfyco/filteredgrid/search/osp");
	}
	
	function activar(){

		$numero  = $this->db->escape($this->input->post('numa'));
		$codigo  = $this->db->escape($this->input->post('codigoa'));
		$usuario = $this->db->escape($this->session->userdata('usuario'));
		
		$mSQL="UPDATE sitems SET despacha=if(despacha='S','N','S'), fdespacha=if(despacha='S',CURDATE(),null), udespacha=$usuario WHERE codigoa=$codigo AND numa=$numero AND tipoa='F' ";
		$a   = $this->db->simple_query($mSQL);
		$can = $this->datasis->dameval("SELECT COUNT(*) FROM sitems WHERE numa=$numero AND tipoa='F' AND despacha='N'");
		if($can==0){
			$mSQL="UPDATE sfac SET fdespacha=CURDATE(), udespacha=$usuario WHERE numero=$numero AND tipo_doc='F'";
			$this->db->simple_query($mSQL);
		}
		//$mSQL="UPDATE sfac SET fdespacha=CURDATE(), udespacha='$usuario' WHERE numero='$numero' AND tipo_doc='F' ";
		//$b=$this->db->simple_query($mSQL);
				
	}
	function parcial($numero){
		$this->rapyd->load("datafilter","datagrid");

		function ractivo($despacha,$numero,$codigoa){
		 $retorna= array(
    			'name'        => $numero,
    			'id'          => $codigoa,
    			'value'       => 'accept'
    			);
		 if($despacha=='S'){
				$retorna['checked']= TRUE;
			}else{
				$retorna['checked']= FALSE;
			}
			return form_checkbox($retorna);
		}
		function cdespacha($cdespacha,$numero,$codigoa,$cana){
		 $retorna= array(
    			'name'        => $numero,
    			'id'          => $codigoa,
    			'value'       => $cdespacha,
    			'size'        => 7
    			);
		 if($cdespacha==' '){
				$retorna['value']= $cdespacha;
			}else{
				$retorna['value']= $cana;
			}
			return form_input($retorna);
		}
		function colum($tipo_doc) {
			if ($tipo_doc=='Anulada')
				return ('<b style="color:red;">'.$tipo_doc.'</b>');
			else
				return ($tipo_doc);
		}

		$grid = new DataGrid("Despacho parcial");
		$grid->db->_escape_char='';
		$grid->db->_protect_identifiers=false;
		
		$grid->db->from('sitems');
		$grid->db->where('tipoa'   ,'F');
		$grid->db->where('numa'    ,$numero);

		$grid->use_function('ractivo');
		$grid->use_function('colum');
		$grid->use_function('cdespacha');

		$grid->column("C&oacute;digo"     ,"codigoa");
		$grid->column("Descripci&oacute;n","desca");
		$grid->column("Cantidad","cana","align=right");
		$grid->column("Precio","<nformat><#preca#></nformat>");
		$grid->column("Total" ,"<nformat><#tota#></nformat>","align=right");
		$grid->column("Cant.Despachado", "<cdespacha><#cdespacha#>|<#numa#>|<#codigoa#>|<#cana#></cdespacha>",'align="center"');
		$grid->column("Despachado", "<ractivo><#despacha#>|<#numa#>|<#codigoa#></ractivo>",'align="center"');
		
		$codigoc=''; 
		$uri = site_url("inscripciones/inscripciones/datagrid/$codigoc/");
    $action = "javascript:window.location='".$uri."';";
    //$grid->button_status("btn_undo", "Undo", $action, "TR", "create", "button");
		$grid->build();
		
		
		$tabla=$grid->output; 


		$script='';
		$url=site_url('ventas/sfacdespfyco/activar');
		//$url1=site_url('ventas/sfacdespfyco/activar1');
		$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
    	       $.ajax({
						  type: "POST",
						  url: "'.$url.'",
						  data: "numa="+this.name+"&codigoa="+this.id,
						  success: function(msg){
						  //alert(msg);						  	
						  }
						});
    	    }).change(); 
			});
			</script>';
	
				
		$attributes = array('id' => 'adespacha');
		$data['content'] =  '';
		if($grid->recordCount>0)
		$atras=anchor("ventas/sfacdespfyco/filteredgrid/search/osp",'Regresar');                                                                   
		$data['content'] .=form_open('').$grid->output.form_close().$script.$atras;
		$data['title']   =  "<h1>Despacho Parcial</h1>";
		$data["head"]    =  script("jquery-1.2.6.pack.js");
		$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function detalle($numero='',$tipoa=''){
			$anchor=anchor('ventas/sfacdespfyco/filteredgrid','Regresar');
			$mSQL_1 = $this->db->query("SELECT a.numa,a.tipoa,a.codigoa,a.desca,a.cana,a.preca,a.tota,a.cdespacha,a.despacha,b.unidad,b.clave FROM sitems AS a JOIN sinv AS b ON a.codigoa=b.codigo WHERE a.numa='$numero' AND a.tipoa='$tipoa'");
			$data2['detalle']= $mSQL_1->result();
			//$data2['anchor']= $anchor; 
			$this->load->view('view_sfacdesp', $data2);
	}
	function guardar(){
				
		$numa       = $this->input->post('numa');		
		$tipoa      = $this->input->post('tipoa');
		$cdespacha  = $this->input->post('cdespacha');
		$despacha   = $this->input->post('despacha');
		$codigoa    = $this->input->post('codigoa');
		$usuario    = $this->db->escape($this->session->userdata('usuario'));
		$ultidespachado = $this->input->post('ultidespachado');
			
		//print_r ($cdespacha);
		//print_r ($cadespacha);
		$cant = $this->datasis->dameval("SELECT COUNT(*)as cant FROM sitems WHERE numa='$numa' AND tipoa='$tipoa'");
    
		 $i=$o=0;
		 
		while($o<$cant){
				
			$despachado = $this->datasis->dameval("SELECT cdespacha+'$ultidespachado[$i]' as despachado FROM sitems WHERE  codigoa='$codigoa[$i]' AND numa='$numa' AND tipoa='$tipoa'");
			$array=array('0'=>$codigoa[$i],'1'=>$cdespacha[$i],'2'=>$despacha[$i]);
		  $mSQL_1 =$this->db->query("UPDATE sitems set ultidespachado='$ultidespachado[$i]',udespacha=$usuario,cdespacha='$despachado',despacha='$despacha[$i]',fdespacha=CURDATE() WHERE codigoa='$codigoa[$i]' AND numa='$numa' AND tipoa='F'");
			$i++;
			$o++;
		}
		redirect("ventas/sfacdespfyco/cerrar/$tipoa/$numa");
	}
	function cerrar($tipoa='',$numa=''){
		
		$data['content'] ='<pre><b style="color:green;">Articulos Despachados</b></pre>';
		$data['content'] .=$comprobante=anchor("formatos/ver/SNOTEXP/$tipoa/$numa",'<pre>Imprimir Nota de Entrega</pre>');
		$data['title']   = "<h1>Despacho Parcial</h1>";
		$data["head"]    = script("jquery-1.2.6.pack.js");
		$data["head"]    = script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
		
	}
	function dolar(){
		$dolar=$this->datasis->dameval("SELECT valor FROM valores WHERE nombre='dolar'");
		$data['content'] = '<h2>'.$dolar.'</h2>';
		$data['title']   = "<h1>Precio del Dolar</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas',$data);
	}
	function instalar(){
		$mSQL="ALTER TABLE `sitems` ADD `cdespacha` DECIMAL NULL";
		$mSQL1="ALTER TABLE `sitems` ADD `ultidespachado` DECIMAL NULL";
		$this->db->simple_query($mSQL);
		$this->db->simple_query($mSQL1);
		echo 'Instalado';
	}
}
?>