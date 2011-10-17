<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//proveed
class pers extends validaciones {

	function pers(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(707,1);
		$mSQL = "CREATE TABLE IF NOT EXISTS `nedu` (`codigo` VARCHAR(4) NOT NULL DEFAULT '',`nivel` VARCHAR(40) NULL DEFAULT NULL, PRIMARY KEY (`codigo`))";
		$this->db->simple_query($mSQL);
		redirect("nomina/pers/extgrid");
	}

	function extgrid(){
		$this->datasis->modulo_id(707,1);
		$script = $this->persextjs();
		$data["script"] = $script;
		$data['title']  = heading('Personal');
		$this->load->view('extjs/pers',$data);
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		$this->datasis->modulo_id(707,1);

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$filter = new DataFilter("Filtro de Personal", 'pers');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		$filter->script->css_class='inputnum';
		
		$filter->cedula = new inputField("C&eacute;dula", "cedula");
		$filter->cedula->size=10;
		$filter->cedula->css_class='inputnum';
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=30;
		
		$filter->apellido = new inputField("Apellido", "apellido");
		$filter->apellido->size=30;
		
		$filter->contrato = new dropdownField("Contrato","contrato");
		$filter->contrato->style ="width:400px;";
		$filter->contrato->option("","");
		$filter->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		
		$filter->divi = new dropdownField("Divisi&oacute;n", "divi");
		$filter->divi->style ="width:250px;";
		$filter->divi->option("","");
		$filter->divi->options("SELECT division,descrip FROM divi ORDER BY division");
				
		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');

		$uri = anchor('nomina/pers/dataedit/show/<#codigo#>','<#codigo#>');
		$uri_2  = anchor('nomina/pers/dataedit/modify/<#codigo#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."nomina/pers/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";
		
		$grid = new DataGrid($mtool);
		$grid->order_by("codigo","asc");
		$grid->per_page = 30;

		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("C&oacute;digo",$uri,'codigo');
		$grid->column_orderby("C&eacute;dula","cedula",'cedula');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Apellidos","apellido",'apellido');
		$grid->column_orderby("Sexo","sexo",'sexo');
		$grid->column_orderby("E.Civil","civil",'civil');
		$grid->column_orderby("Direcci&oacute;n","direc1",'direc1');
		$grid->column_orderby("Telefono","telefono",'telefono');
		$grid->column_orderby("F.Nacimiento","<dbdate_to_human><#nacimi#></dbdate_to_human>",'nacimi');
		$grid->column_orderby("F.Ingreso","<dbdate_to_human><#ingreso#></dbdate_to_human>",'ingreso');
		$grid->column_orderby("Sueldo","<nformat><#sueldo#></nformat>",'sueldo');
		
		//$grid->add("nomina/pers/dataedit/create");
		$grid->build('datagridST');
		
		//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************


		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['extras']  = $extras;		

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
		$data['title']  = heading('Personal');
		$data['script'] = script('jquery.js').script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["script"].= script('superTables.js');
		$data['head']   = $this->rapyd->get_head();
		
		$this->load->view('view_ventanas', $data);	
	}
	
	function dataedit(){
		
		$this->rapyd->load("dataedit2");
		$consulrif=$this->datasis->traevalor('CONSULRIF');
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		
		function consulrif(){
				vrif=$("#rif").val();
				if(vrif.length==0){
					alert("Debe introducir primero un RIF");
				}else{
					vrif=vrif.toUpperCase();
					$("#rif").val(vrif);
					window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
				}
		}
		
		';	
				
		$edit = new DataEdit2("Personal", "pers");
		$edit->back_url = site_url("nomina/pers/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");

		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
					  
		$sucu=array(
		'tabla'   =>'sucu',
		'columnas'=>array(
		'codigo'  =>'C&oacute;digo de Sucursal',
		'sucursal'=>'Sucursal'),
		'filtro'  =>array('codigo'=>'C&oacute;digo de Sucursal','sucursal'=>'Sucursal'),
		'retornar'=>array('codigo'=>'sucursal'),
		'titulo'  =>'Buscar Sucursal');
		
		$boton=$this->datasis->modbus($sucu);
		
		$cargo=array(
		'tabla'   =>'carg',
		'columnas'=>array(
		'cargo'  =>'C&oacute;digo de Cargo',
		'descrip'=>'Descripcion'),
		'filtro'  =>array('codigo'=>'C&oacute;digo de Cargo','descrip'=>'Descripcion'),
		'retornar'=>array('cargo'=>'cargo'),
		'titulo'  =>'Buscar Cargo');
		
		$boton1=$this->datasis->modbus($cargo);
		
		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'enlace'),
		'titulo'  =>'Buscar Empleado');
		
		$cboton=$this->datasis->modbus($scli);
		
		$edit->codigo =  new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule="trim|required|callback_chexiste";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=15;
		$edit->codigo->size=16;
		
		$edit->nacional = new dropdownField("C&eacute;dula", "nacional");
		$edit->nacional->style = "width:110px;";
		$edit->nacional->option("V","Venezolano");
		$edit->nacional->option("E","Extranjero");
		$edit->nacional->group = "Datos del Trabajador";
		 
		$edit->cedula =  new inputField("", "cedula");
		$edit->cedula->size = 14;
		$edit->cedula->maxlength= 8;
		$edit->cedula->in = "nacional";
		$edit->cedula->rule="trim|required";
		$edit->cedula->css_class='inputnum';
			
		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick=""> Consultar RIF en el SENIAT</a>';
		$edit->rif =  new inputField("RIF", "rif");
		//$edit->rif->mode="autohide";
		$edit->rif->rule = "trim|strtoupper|callback_chrif";
		$edit->rif->append($lriffis);
		$edit->rif->maxlength=10;
		$edit->rif->size = 13;
		$edit->rif->group = "Datos del Trabajador";
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->group = "Datos del Trabajador";
		$edit->nombre->size = 40;
		$edit->nombre->maxlength=30;
		$edit->nombre->rule="trim|required|strtoupper";
		
		$edit->apellido = new inputField("Apellidos", "apellido");
		$edit->apellido->group = "Datos del Trabajador";
		$edit->apellido->size = 40;
		$edit->apellido->maxlength=30;
		//$edit->apellido->in = "nombre";
		$edit->apellido->rule="trim|required|strtoupper";
		
		$edit->sexo = new dropdownField("Sexo", "sexo");
		$edit->sexo->style = "width:60px;";
		$edit->sexo->option("F","F");
		$edit->sexo->option("M","M");
		$edit->sexo->group = "Datos del Trabajador";
		
		//$edit->label1 = new freeField("EC","EC","<id class='littletableheader'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Estado Civil&nbsp;&nbsp; </id>");
		//$edit->label1->in = "sexo";
		
		$edit->civil = new dropdownField("Estado Civil", "civil");
		$edit->civil->style = "width:100px;";
		$edit->civil->option("S","Soltero");
		$edit->civil->option("C","Casado");
		$edit->civil->option("D","Divorciado");
		$edit->civil->option("V","Viudo");
		$edit->civil->group = "Datos del Trabajador";
		//$edit->civil->in = "sexo";
		
		$edit->direc1 = new inputField("Direcci&oacute;n", "direc1");
		$edit->direc1->group = "Datos del Trabajador";
		$edit->direc1->size =40;
		$edit->direc1->maxlength=30;
		$edit->direc1->rule="trim|strtoupper";
		
		$edit->direc2 = new inputField("&nbsp;", "direc2");
		$edit->direc2->size =40;
		$edit->direc2->group = "Datos del Trabajador";
		$edit->direc2->maxlength=30; 
		$edit->direc2->rule="trim|strtoupper";
		
		$edit->direc3 = new inputField("&nbsp;", "direc3");
		$edit->direc3->size =40;
		$edit->direc3->group = "Datos del Trabajador";
		$edit->direc3->maxlength=30;
		$edit->direc3->rule="trim|strtoupper";
		
		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
		$edit->telefono->size =40;
		$edit->telefono->group = "Datos del Trabajador";
		$edit->telefono->maxlength=30;
		$edit->telefono->rule="trim|strtoupper";
		
		$edit->email = new inputField("Email","email");
		$edit->email->size =50;
		$edit->email->group = "Datos del Trabajador";
		$edit->email->maxlength=50;
		$edit->email->rule="trim";
		/*
		$edit->posicion = new dropdownField("Tipo de Escritura" ,"escritura");
		$edit->posicion->option("","");                                                 
		$edit->posicion->options("SELECT codigo,posicion FROM posicion  ORDER BY codigo");
		$edit->posicion->group = "Datos del Trabajador";
		$edit->posicion->rule="trim|strtoupper";
		$edit->posicion->style ="width:170px;";
		*/
		
		$edit->civil = new dropdownField("Estado Civil", "civil");
		$edit->civil->style = "width:100px;";
		$edit->civil->option("S","Soltero");
		$edit->civil->option("C","Casado");
		$edit->civil->option("D","Divorciado");
		$edit->civil->option("V","Viudo");
		$edit->civil->group = "Datos del Trabajador";
		
		$edit->profes = new dropdownField("Profesion","profes");
		$edit->profes->options("SELECT codigo,profesion FROM prof ORDER BY profesion");
		
		$edit->nacimi = new DateOnlyField("Fecha de Nacimiento", "nacimi","d/m/Y");
		$edit->nacimi->size = 12;
		$edit->nacimi->group = "Datos del Trabajador"; 
		$edit->nacimi->rule="trim|chfecha";
		
		$edit->sucursal = new inputField("Sucursal", "sucursal");
		$edit->sucursal->size =4;
		$edit->sucursal->maxlength=2;
		$edit->sucursal->group = "Relaci&oacute;n Laboral";
		$edit->sucursal->append($boton);
		$edit->sucursal->rule="trim|strtoupper";
				
		$edit->divi = new dropdownField("Divisi&oacute;n", "divi");
		$edit->divi->style ="width:250px;";
		$edit->divi->option("","");
		$edit->divi->options("SELECT division,descrip FROM divi ORDER BY division");
		$edit->divi->onchange = "get_depto();";
		$edit->divi->group = "Relaci&oacute;n Laboral";
		
		$edit->depa = new dropdownField("Departamento", "depto");
		$edit->depa->style ="width:250px;";
		$edit->depa->option("","");
		if($edit->_status=='modify' || $edit->_status=='show' ){
		$divi=$edit->getval('divi');
			if($divi!==FALSE){
				$edit->depa->options("SELECT departa,depadesc FROM depa where division='$divi' ORDER BY division");
			}else{
				$edit->depa->option("Seleccione un Division");
			}
		}
		$edit->depa->group = "Relaci&oacute;n Laboral";	
		
		$edit->contrato = new dropdownField("Contrato","contrato");
		$edit->contrato->style ="width:400px;";
		$edit->contrato->option("","");
		$edit->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		$edit->contrato->group = "Relaci&oacute;n Laboral";
		
		$edit->vencimiento = new DateField("Vencimiento", "vence","d/m/Y");
		$edit->vencimiento->size = 12;
		$edit->vencimiento->group = "Relaci&oacute;n Laboral";
		$edit->vencimiento->rule="trim|chfecha";
		
		$edit->cargo = new inputField("Cargo", "cargo");
		$edit->cargo->group = "Relaci&oacute;n Laboral";
		$edit->cargo->size =11;
		$edit->cargo->maxlength=8;
		$edit->cargo->append($boton1);
		$edit->cargo->rule="trim";                                   
		
		$edit->enlace = new inputField("Enlace","enlace");
		$edit->enlace->size =11;
		$edit->enlace->maxlength=5;
		$edit->enlace->group = "Relaci&oacute;n Laboral";
		$edit->enlace->append($cboton); 
		$edit->enlace->rule="trim|strtoupper";
						
		$edit->sso = new inputField("Nro. Seguro Social", "sso");
		$edit->sso->size =13;
		$edit->sso->maxlength=11;
		$edit->sso->group = "Relaci&oacute;n Laboral"; 
    //$edit->sso->rule="trim|numeric"; 
		$edit->sso->css_class='inputnum';
		
		$edit->observa = new textareaField("Observaci&oacute;n", "observa");
		$edit->observa->rule = "trim";
		$edit->observa->cols = 70;
		$edit->observa->rows =3;
		$edit->observa->group = "Relaci&oacute;n Laboral"; 
		
		$edit->ingreso = new DateField("Fecha de Ingreso", "ingreso","d/m/Y");
		$edit->ingreso->size = 12;
		$edit->ingreso->group = "Relaci&oacute;n Laboral";
		$edit->ingreso->rule="trim|chfecha";
		
		$edit->label2 = new freeField("Edo. C","edoci","<id class='littletableheader'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha de Retiro&nbsp;&nbsp; </id>");
		$edit->label2->in = "ingreso";
		
		$edit->retiro =  new DateField("Fecha de Retiro", "retiro","d/m/Y");    
		$edit->retiro->size = 12;
		$edit->retiro->in = "ingreso";
		$edit->retiro->rule="trim|chfecha";
		
		/*$edit->trabaja = new dropdownField("Tipo de Trabajador","tipot");
		$edit->trabaja->option("","");
		$edit->trabaja->options("SELECT codigo,tipo  FROM tipot ORDER BY codigo");
		$edit->trabaja->group = "Relaci&oacute;n Laboral";
		$edit->trabaja->style = "width:200px;";*/
		
		$edit->tipo = new dropdownField("Tipo de N&oacute;mina","tipo");
		$edit->tipo->option("","");
		$edit->tipo->options(array("Q"=> "Quincenal","M"=>"Mensual","S"=>"Semanal"));
		$edit->tipo->group = "Relaci&oacute;n Laboral";
		$edit->tipo->style = "width:100px;";
		
		$edit->dialib = new inputField("Dias libres", "dialib");
		$edit->dialib->group = "Relaci&oacute;n Laboral";
		$edit->dialib->size =4;
		$edit->dialib->maxlength=2;
		$edit->dialib->rule="trim|numeric";
		$edit->dialib->css_class='inputnum';
		
		$edit->label3 = new freeField("DL","DL","<id class='littletableheader'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dias Laborables&nbsp;&nbsp; </id>");
		$edit->label3->in = "dialib";
		
		$edit->dialab =  new inputField("Dias laborables", "dialab");
		$edit->dialab->group = "Relaci&oacute;n Laboral";
		$edit->dialab->size =4;
		$edit->dialab->maxlength=2;
		$edit->dialab->in = "dialib";
		//$edit->dialab->rule="trim|numeric";
		//$edit->dialab->css_class='inputnum';
		
		$edit->status = new dropdownField("Estatus", "status");
		$edit->status->option("","");
		$edit->status->options(array("A"=> "Activo","V"=>"Vacaciones","R"=>"Retirado","I"=>"Inactivo","P"=>"Permiso"));
		$edit->status->group = "Relaci&oacute;n Laboral";
		$edit->status->style = "width:100px;";
		
		$edit->carnet =  new inputField("Nro. Carnet", "carnet");
		$edit->carnet->size = 13;
		$edit->carnet->maxlength=10;
		$edit->carnet->group = "Relaci&oacute;n Laboral";
		$edit->carnet->rule="trim"; 
		                        
		$edit->turno = new dropdownField("Turno", "turno");
		$edit->turno->option("","");
		$edit->turno->options(array("D"=> "Diurno","N"=>"Nocturno"));
		$edit->turno->group = "Relaci&oacute;n Laboral";
		$edit->turno->style = "width:100px;";
		
		$edit->horame  = new inputField("Turno Ma�ana","horame");
		$edit->horame->maxlength=8;
		$edit->horame->size=10;
		$edit->horame->rule='trim|callback_chhora';
		$edit->horame->append('hh:mm:ss');
		$edit->horame->group="Relaci&oacute;n Laboral";

		$edit->horams  = new inputField("Turno Ma�ana","horams");
		$edit->horams->maxlength=8;
		$edit->horams->size=10;
		$edit->horams->rule='trim|callback_chhora';
		$edit->horams->append('hh:mm:ss');
		$edit->horams->in="horame";
		$edit->horams->group="Relaci&oacute;n Laboral";
		
		$edit->horate  = new inputField("Turno Tarde","horate");
		$edit->horate->maxlength=8;
		$edit->horate->size=10;
		$edit->horate->rule='trim|callback_chhora';
		$edit->horate->append('hh:mm:ss');
		$edit->horate->group="Relaci&oacute;n Laboral";
              
		$edit->horats  = new inputField("Turno Tarde","horats");
		$edit->horats->maxlength=8;
		$edit->horats->size=10;
		$edit->horats->rule='trim|callback_chhora';
		$edit->horats->append('hh:mm:ss');
		$edit->horats->in="horate";
		$edit->horats->group="Relaci&oacute;n Laboral";
		
		$edit->sueldo = new inputField("Sueldo","sueldo");
		$edit->sueldo->group = "Relaci&oacute;n Laboral";
		$edit->sueldo->size =10;
		$edit->sueldo->maxlength=15;
		$edit->sueldo->rule="trim|numeric";
		$edit->sueldo->css_class='inputnum';
	
		$edit->tipocuent = new dropdownField("Tipo Cuenta", "tipoe");
		$edit->tipocuent->option("","");
		$edit->tipocuent->options(array("A"=> "Ahorro","C"=>"Corriente"));
		$edit->tipocuent->group = "Datos Cuenta Bancaria";
		$edit->tipocuent->style = "width:100px;";
		
		$edit->cuentab = new inputField("Nro. Cuenta", "cuentab");
		$edit->cuentab->group = "Datos Cuenta Bancaria";
		$edit->cuentab->size =40;
		$edit->cuentab->maxlength=40;
		//$edit->cuentab->rule="trim|numeric";
		//$edit->cuentab->css_class='inputnum';
		
		$edit->vari1 = new inputField("Retenci&oacute;n SSO", "vari1");
		$edit->vari1->group = "Variables";
		$edit->vari1->size =16;
		$edit->vari1->maxlength=14;
		$edit->vari1->rule="trim|numeric";
		$edit->vari1->css_class='inputnum';
		
		$edit->vari2 = new inputField("Retenci&oacute;n FAOV", "vari2");
		$edit->vari2->group = "Variables";
		$edit->vari2->size =16;
		$edit->vari2->maxlength=14;
		$edit->vari2->rule="trim|numeric";
		$edit->vari2->css_class='inputnum';
		
		$edit->vari3 = new inputField("Retenci&oacute;n ISLR", "vari3");
		$edit->vari3->group = "Variables";
		$edit->vari3->size =16;
		$edit->vari3->maxlength=14;
		$edit->vari3->rule="trim|numeric";
		$edit->vari3->css_class='inputnum';
		        
		$edit->vari4 = new inputField("Variable 4", "vari4");
		$edit->vari4->group = "Variables";
		$edit->vari4->size =12;
		$edit->vari4->maxlength=11;
		$edit->vari4->rule="trim|numeric";
		$edit->vari4->css_class='inputnum';
		      
		$edit->vari5 = new DateField("Variable 5", "vari5");
		$edit->vari5->group = "Variables";
		$edit->vari5->size =12;
		$edit->vari5->maxlength=12;
		$edit->vari5->rule="trim|chfecha";
		
		$edit->vari6 = new inputField("Variable 6", "vari6");
		$edit->vari6->group = "Variables";
		$edit->vari6->size =16;
		$edit->vari6->maxlength=14;
		$edit->vari6->rule="trim|numeric";
		$edit->vari6->css_class='inputnum';
		    
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$link=site_url('nomina/pers/depto');
	$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_depto(){
				var divi=$("#divi").val();
				$.ajax({
					url: "$link"+'/'+divi,
					success: function(msg){
						$("#td_depto").html(msg);								
					}
				});
									//alert(divi);
			} 
		</script>
script;

		$conten["form"]  =& $edit;
		$data['content'] = $this->load->view('view_pers', $conten,true);
		//$data['content'] = $edit->output; 
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Personal</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function depto($divi=NULL){
		$this->rapyd->load("fields");
		$depa = new dropdownField("Departamento", "depto");
		$depa->status = "modify";
		$depa->style ="width:400px;";
		//echo 'de nuevo:'.$tipoa;
		if ($divi!==false){
			$depa->options("SELECT departa,depadesc FROM depa where division='$divi' ORDER BY division");			
		}else{
			$depa->option("Seleccione un Division");
		}
		$depa->build(); 
		echo $depa->output;
	}
	
	function _pre_del($do) {
		$codigo=$do->get('codigo');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM nomina WHERE codigo='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM asig   WHERE codigo='$codigo'");
	
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Trabajador con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}
	
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('pers',"PERSONAL $codigo NOMBRE  $nombre CREADO");
	}
	
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('pers',"PERSONAL $codigo NOMBRE  $nombre  MODIFICADO");
	}
	
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('pers',"PERSONAL $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM pers WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"Personal con el codigo $codigo nombre $nombre ya existe");
			return FALSE;
		}else {
			return TRUE;
		}	
	}
	
	function instalar(){
		$mSQL1="ALTER TABLE `pers` ADD `email` VARCHAR(50) NULL";
		$this->db->simple_query($mSQL1);
		$mSQL3="ALTER TABLE `pers` ADD`tipoe` VARCHAR(10)";
		$this->db->simple_query($mSQL3);
		$mSQL4="ALTER TABLE `pers` ADD `escritura` VARCHAR(25),ADD `rif` VARCHAR(15)";
		$this->db->simple_query($mSQL4);
		$mSQL5="ALTER TABLE `pers` ADD `observa` TEXT ";
		$this->db->simple_query($mSQL5);
		$mSQL6="CREATE TABLE tipot(codigo int(10) unsigned NOT NULL AUTO_INCREMENT,	`tipo` varchar(50) DEFAULT NULL,PRIMARY KEY (`codigo`) )";
 		$this->db->simple_query($mSQL6);	 
		$mSQL7="CREATE TABLE `posicion`(`codigo` varchar(10) NOT NULL,`posicion` varchar(30) DEFAULT NULL,PRIMARY KEY (`codigo`))";
		$this->db->simple_query($mSQL7);
		$mSQL8="CREATE TABLE `tipoe` (`codigo` varchar(10) NOT NULL DEFAULT '', `tipo` varchar(50) DEFAULT NULL, PRIMARY KEY (`codigo`))"; 
		$this->db->simple_query($mSQL8);
		$mSQL9="ALTER TABLE pers`datasis` ADD COLUMN `turno` CHAR(2) NULL,ADD COLUMN `horame` VARCHAR(10),ADD COLUMN `horams` VARCHAR(10),ADD COLUMN `horate` VARCHAR(10),ADD COLUMN `horats` VARCHAR(10)";
		$this->db->simple_query($mSQL9);
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : 'contrato';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;


		$where = "";

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//Dummy Where. 
				$where = "pers.codigo IS NOT NULL ";
				$qs = "";
				for ($i=0;$i<count($filter);$i++){
					switch($filter[$i]['type']){
					case 'string' : $qs .= " AND pers.".$filter[$i]['field']." LIKE '%".$filter[$i]['value']."%'"; 
						Break;
					case 'list' :
						if (strstr($filter[$i]['value'],',')){
							$fi = explode(',',$filter[$i]['value']);
							for ($q=0;$q<count($fi);$q++){
								$fi[$q] = "'".$fi[$q]."'";
							}
							$filter[$i]['value'] = implode(',',$fi);
								$qs .= " AND pers.".$filter[$i]['field']." IN (".$filter[$i]['value'].")";
						}else{
							$qs .= " AND pers.".$filter[$i]['field']." = '".$filter[$i]['value']."'";
						}
						Break;
					case 'boolean' : $qs .= " AND pers.".$filter[$i]['field']." = ".($filter[$i]['value']); 
						Break;
					case 'numeric' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND pers.".$filter[$i]['field']." != ".$filter[$i]['value']; 
								Break;
							case 'eq' : $qs .= " AND pers.".$filter[$i]['field']." = ".$filter[$i]['value']; 
								Break;
							case 'lt' : $qs .= " AND pers.".$filter[$i]['field']." < ".$filter[$i]['value']; 
								Break;
							case 'gt' : $qs .= " AND pers.".$filter[$i]['field']." > ".$filter[$i]['value']; 
								Break;
						}
						Break;
					case 'date' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND pers.".$filter[$i]['field']." != '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'eq' : $qs .= " AND pers.".$filter[$i]['field']." = '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'lt' : $qs .= " AND pers.".$filter[$i]['field']." < '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'gt' : $qs .= " AND pers.".$filter[$i]['field']." > '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
						}
						Break;
					}
				}
				$where .= $qs;
			}
		}
		
		$this->db->_protect_identifiers=false;
		//$this->db->select('pers.codigo, pers.nacional, pers.cedula, pers.nombre, pers.apellido, pers.civil, pers.sexo, pers.carnet, pers.status, pers.tipo, pers.contrato, pers.ingreso, pers.sueldo, pers.retiro, pers.vence, pers.direc1, pers.direc2, pers.direc3, pers.telefono, pers.nacimi, vari1, vari2, vari3, vari4, vari5, vari6, CONCAT("(",pers.contrato,") ",noco.nombre) nomcont');
		$this->db->select('pers.*, CONCAT("(",pers.contrato,") ",noco.nombre) nomcont');

		$this->db->from('pers');
		$this->db->join('noco', 'pers.contrato=noco.codigo');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		$this->db->order_by( 'contrato', 'asc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $this->db->count_all('pers');

		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$codigo = $data['data']['codigo'];
		$nombre = trim($data['data']['nombre']).' '.$data['data']['apellido'];;

		unset($campos['nomcont']);
		unset($campos['id']);
		
		//Evita la hora de las fechas
		$campos['nacimi']  = substr($campos['nacimi'], 0,10);
		$campos['ingreso'] = substr($campos['ingreso'],0,10);
		$campos['retiro']  = substr($campos['retiro'], 0,10);
		$campos['vence']   = substr($campos['vence'],  0,10);
		$campos['vari5']   = substr($campos['vari5'],  0,10);
		
		$mHay = $this->datasis->dameval("SELECT count(*) FROM pers WHERE codigo='".$data['data']['codigo']."'");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe ese codigo'}";
		} else {
			$mSQL = $this->db->insert_string("pers", $campos );
			$this->db->simple_query($mSQL);
			logusu('pers',"PERSONAL $codigo NOMBRE  $nombre CREADO");
			echo "{ success: true, message: ".$data['data']['codigo']."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $data['data']['codigo'];
		$nombre = trim($data['data']['nombre']).' '.$data['data']['apellido'];;

		unset($campos['nomcont']);
		unset($campos['codigo']);
		
		//Evita la hora de las fechas
		//$campos['nacimi']  = substr($campos['nacimi'], 0,10);
		//$campos['ingreso'] = substr($campos['ingreso'],0,10);
		//$campos['retiro']  = substr($campos['retiro'], 0,10);
		//$campos['vence']   = substr($campos['vence'],  0,10);
		//$campos['vari5']   = substr($campos['vari5'],  0,10);
		
		//print_r($campos);
		$mSQL = $this->db->update_string("pers", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('pers',"PERSONAL $codigo NOMBRE  $nombre MODIFICADO");
		echo "{ success: true, message: 'Trabajador Modificado'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $data['data']['codigo'];
		$nombre = trim($data['data']['nombre']).' '.$data['data']['apellido'];;

		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM nomina WHERE codigo='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM asig   WHERE codigo='$codigo'");

		if ($chek > 0){
			echo "{ success: false, message: 'Trabajador con Movimiento no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM pers WHERE codigo='$codigo'");
			logusu('pers',"PERSONAL $codigo NOMBRE  $nombre ELIMINADO");
			echo "{ success: true, message: 'Trabajador Eliminado'}";
		}
	}

//****************************************************************8
//
//
//
//****************************************************************8
	function persextjs(){

		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">TRABAJADORES</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';

		$mSQL = "SELECT codigo, CONCAT(codigo,' ',nombre) nombre, tipo FROM noco WHERE tipo<>'O' ORDER BY codigo";
		$contratos = $this->datasis->llenacombo($mSQL);
		
		$mSQL = "SELECT division, CONCAT(division,' ',descrip) descrip FROM divi ORDER BY division";
		$divi= $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT departa, CONCAT(departa,' ',depadesc) descrip FROM depa ORDER BY departa";
		$depto= $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT cargo, CONCAT(cargo,' ',descrip) descrip FROM carg ORDER BY cargo";
		$cargo= $this->datasis->llenacombo($mSQL);;
		
		$mSQL = "SELECT codigo, CONCAT(codigo,' ',sucursal) descrip FROM sucu ORDER BY codigo";
		$sucu = $this->datasis->llenacombo($mSQL);

		$mSQL   = "SELECT codigo, profesion FROM prof ORDER BY profesion";
		$profes = $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT codigo, nivel FROM nedu ORDER BY codigo ";
		$niveled = $this->datasis->llenacombo($mSQL);

		$script = "
<script type=\"text/javascript\">
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

Ext.require([
	'Ext.grid.*',
	'Ext.ux.grid.FiltersFeature',
	'Ext.data.*',
	'Ext.util.*',
	'Ext.state.*',
	'Ext.form.*',
	'Ext.window.MessageBox',
	'Ext.tip.*',
	'Ext.ux.CheckColumn',
	'Ext.toolbar.Paging'
]);

var registro;
var urlApp = '".base_url()."';

// Define our data model
var Empleados = Ext.regModel('Empleados', {
	fields: ['id','codigo','nacional','cedula','nombre','apellido','civil','sexo', 'carnet', 'status', 'tipo' ,'contrato','ingreso','retiro','vence', 'direc1', 'direc2', 'direc3', 'telefono','sueldo','nacimi','vari1','vari2','vari3','vari4','vari5','vari6','divi','depto', 'sucursal','cargo','dialab','dialib','niveled','sso', 'profes','nomcont'],
	validations: [
		{ type: 'length', field: 'codigo',   min: 1 },
		{ type: 'length', field: 'nacional', min: 1 }, 
		{ type: 'length', field: 'cedula',   min: 6 }, 
		{ type: 'length', field: 'nombre',   min: 3 }
	],
	proxy: {
		type: 'ajax',
		noCache: false,
		api: {
			read   : urlApp + 'nomina/pers/grid',
			create : urlApp + 'nomina/pers/crear',
			update : urlApp + 'nomina/pers/modificar' ,
			destroy: urlApp + 'nomina/pers/eliminar',
			method: 'POST'
			},
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data',
			messageProperty: 'message',
			totalProperty: 'results'
			},
		writer: {
			type: 'json',
			root: 'data',
			writeAllFields: true,
			callback: function( op, suc ) {
				Ext.Msg.Alert('que paso');
				}
			},
		listeners: {
			exception: function( proxy, response, operation) {
				Ext.MessageBox.show({
					title: 'EXCEPCION REMOTA',
					msg: operation.getError(),
					icon: Ext.MessageBox.ERROR,
					buttons: Ext.Msg.OK
				});
			}
		}
	}
});

//Data Store
var storePers = Ext.create('Ext.data.Store', {
	model: 'Empleados',
	pageSize: 50,
	remoteSort: true,
	autoLoad: false,
	autoSync: true,
	groupField: 'nomcont',
	method: 'POST',
	listeners: {
		write: function(mr,re, op) {
			Ext.Msg.alert('Aviso','Registro Guardado '+re.success)
		}
	}
});


//Column Model
var colPers = 
	[
		{ header: 'Codigo',     width:  60, sortable: true, dataIndex: 'codigo',   field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Status',     width:  60, sortable: true, dataIndex: 'status',   field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Nac',        width:  60, sortable: true, dataIndex: 'nacional', field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Cedula',     width:  80, sortable: true, dataIndex: 'cedula',   field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Nombre',     width: 150, sortable: true, dataIndex: 'nombre',   field:  { type: 'textfield' }, filter: { type: 'string'  } }, //editor: 'textfield' }, 
		{ header: 'Apellidos',  width: 150, sortable: true, dataIndex: 'apellido', field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Contrato',   width:  60, sortable: true, dataIndex: 'contrato', field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Ingreso',    width:  70, sortable: true, dataIndex: 'ingreso',  field:  { type: 'date'      }, filter: { type: 'date'    }, renderer: Ext.util.Format.dateRenderer('d/m/Y') }, 
		{ header: 'Sueldo',     width: 120, sortable: true, dataIndex: 'sueldo',   field:  { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }, 
		{ header: 'Nacimiento', width:  70, sortable: true, dataIndex: 'nacimi',   field:  { type: 'date'      }, filter: { type: 'date'    } }, 
		{ header: 'Telefono',   width: 100, sortable: true, dataIndex: 'telefono', field:  { type: 'textfield' }, filter: { type: 'string'  } },
		{ header: '".$this->datasis->traevalor('NOMVARI1')."',     width: 60, sortable: true, dataIndex: 'vari1',   field:  { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0.00') }, 
		{ header: '".$this->datasis->traevalor('NOMVARI2')."',     width: 60, sortable: true, dataIndex: 'vari2',   field:  { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0.00') }, 
		{ header: '".$this->datasis->traevalor('NOMVARI3')."',     width: 60, sortable: true, dataIndex: 'vari3',   field:  { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0.00') }
	];

var ci = {
	layout: 'column',
	defaults: {columnWidth:0.5, layout: 'form', border: false, xtype: 'panel'},
	items: [{
		defaults: { anchor: '100%' },
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Nacional',
				name: 'nacional',
				allowBlank: false
			}]
		},{
		defaults: { anchor: '100%' },
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Cedula',
				name: 'cedula',
				allowBlank: false
			}]
		}]
	};
	
var win;

// Main 
Ext.onReady(function(){
	function showContactForm() {
		if (!win) {
			// Create Form
			var writeForm = Ext.define('Pers.Form', {
				extend: 'Ext.form.Panel',
				alias:  'widget.writerform',
				result: function(res){
					alert('Resultado');
				},
				requires: ['Ext.form.field.Text'],
				initComponent: function(){
					Ext.apply(this, {
						iconCls: 'icon-user',
						frame: true, 
						title: 'Ficha del Trabajador', 
						bodyPadding: 3,
						//layout: 'fit',
						fieldDefaults: { 
							//anchor: '100%',
    							labelAlign: 'right' 
						}, 
						items: [{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Codigo',    labelWidth:60, name: 'codigo',   allowBlank: false, columnWidth : 0.25, id: 'codigo' },
									{ xtype: 'combo',       fieldLabel: 'Contrato',  labelWidth:60, name: 'contrato', store: [".$contratos."], columnWidth : 0.50 },
									{ xtype: 'combo',       fieldLabel: 'Status.',   labelWidth:60, name: 'status',   store: [['A','Activo'],['V','Vacaciones'],['R','Retirado'],['P', 'Permiso'],['I','Inactivo']], columnWidth: 0.25 },
									{ xtype: 'combo',       fieldLabel: 'Nacional.', labelWidth:60, name: 'nacional', store: [['V','Venezolano'],['E','Extranjero'],['P', 'Pasaporte']], columnWidth: 0.25 },
									{ xtype: 'textfield',   fieldLabel: 'Cedula',    labelWidth:60, name: 'cedula',   allowBlank: false, columnWidth: 0.25  },
									{ xtype: 'combo',       fieldLabel: 'Edo.Civil', labelWidth:60, name: 'civil',    store: [['S','Soltero'],['C','Casado'],['D', 'Divorciado'],['V', 'Viudo']], columnWidth: 0.25 },
									{ xtype: 'combo',       fieldLabel: 'Sexo',      labelWidth:60, name: 'sexo',     store: [['F','Femenino'],['M','Masculino'],['O', 'Otro']], columnWidth: 0.25 },
									{ xtype: 'textfield',   fieldLabel: 'Nombre',    labelWidth:60, name: 'nombre',   allowBlank: false, columnWidth: 0.50 },
									{ xtype: 'textfield',   fieldLabel: 'Apellido',  labelWidth:60, name: 'apellido', allowBlank: false, columnWidth: 0.50 }
								]
							},
							// tabpanel
							{
								xtype:'tabpanel',
								activeItem: 0,
								border: false,
								//anchor: '100% 100%',
								deferredRender: false,
								Height: 200,
								// tabs
								defaults: {bodyStyle:'padding:5px',hideMode:'offsets'},
								items:[{
									title: 'Valores',
									autoScroll:true,
									defaults:{anchor:'-20'},
									items: [{
										defaults: {xtype:'fieldset', columnWidth : 0.49  },
										layout: 'column',
										border: false,
										autoHeight:true,
										style:'padding:4px',
										items: [{
											title:'Datos',
											columnWidth : 0.50, 
											//layout: 'fit',
											//defaults:{anchor:'-20'},
											items: [
												{ xtype: 'textfield',   fieldLabel: 'Dias Laborables',  labelWidth:120, name: 'dialab',  allowBlank: true, width:230 },
												{ xtype: 'textfield',   fieldLabel: 'Dias Libres',      labelWidth:120, name: 'dialib',  allowBlank: true, width:230 },
												{ xtype: 'numberfield', fieldLabel: 'Sueldo ',          labelWidth:120, name: 'sueldo',  hideTrigger: true, fieldStyle: 'text-align: right', width:230,renderer : Ext.util.Format.numberRenderer('0,000.00') },
											]
										},{
											title:'Fechas',
											//defaults:{anchor:'-20'},
											items: [
												{ xtype: 'datefield', fieldLabel: 'Fecha de Ingreso',   labelWidth:120, name: 'ingreso', width:230, format: 'd/m/Y', submitFormat: 'Y-m-d', value: new Date() },
												{ xtype: 'datefield', fieldLabel: 'Fecha Vencimiento',  labelWidth:120, name: 'vence',   width:230, format: 'd/m/Y', submitFormat: 'Y-m-d' },
												{ xtype: 'datefield', fieldLabel: 'Fecha de Retiro',    labelWidth:120, name: 'retiro',  width:230, format: 'd/m/Y', submitFormat: 'Y-m-d' },
											]
										},{
											title:'Ubicacion',
											columnWidth : 0.99, 
											//defaults:{anchor:'-20'},
											layout: 'column',
											items: [
												{ xtype: 'combo', fieldLabel: 'Division',     labelWidth: 90, name: 'divi',     width:310, store: [".$divi."] },
												{ xtype: 'combo', fieldLabel: 'Sucursal',     labelWidth: 60, name: 'sucursal', width:250, store: [".$sucu."] },
												{ xtype: 'combo', fieldLabel: 'Departamento', labelWidth: 90, name: 'depto',    width:310, store: [".$depto."] },
												{ xtype: 'combo', fieldLabel: 'Cargo',        labelWidth: 60, name: 'cargo',    width:250, store: [".$cargo."] },
											]
										}]
									}]
								},{
									title: 'Direccion',
									autoScroll:true,
									defaults:{anchor:'-20'},
									items:[{
										defaults: {xtype:'fieldset', columnWidth : 0.49  },
										layout: 'column',
										border: false,
										autoHeight:true,
										style:'padding:4px',
										items: [{
											title:'Direccion',
											columnWidth : 0.50, 
											//layout: 'fit',
											//defaults:{anchor:'-20'},
											items: [
												{ xtype: 'textfield', fieldLabel: 'Direccion', labelWidth:60, name: 'direc1',   allowBlank: true, width: 280 },
												{ xtype: 'textfield', fieldLabel: '      ',    labelWidth:60, name: 'direc2',   allowBlank: true, width: 280 },
												{ xtype: 'textfield', fieldLabel: '      ',    labelWidth:60, name: 'direc3',   allowBlank: true, width: 280 },
												{ xtype: 'textfield', fieldLabel: 'Telefono',  labelWidth:60, name: 'telefono', allowBlank: true, width: 280 },
											]
										},{
											title:'Otros',
											columnWidth : 0.49, 
											//defaults:{anchor:'-20'},
											layout: 'column',
											items: [
												{ xtype: 'textfield', fieldLabel: 'Numero de Carnet',  labelWidth:110, name: 'carnet',  allowBlank: true, width:260 },
												{ xtype: 'textfield', fieldLabel: 'Nro Seguro Social', labelWidth:110, name: 'sso',  allowBlank: true, width:260 },
												{ xtype: 'datefield', fieldLabel: 'Fecha Nacimiento',  labelWidth:110, name: 'nacimi',  width:260, format: 'd/m/Y', submitFormat: 'Y-m-d' },
												{ xtype: 'combo',     fieldLabel: 'Nivel Instruccion', labelWidth:110, name: 'niveled',  width:260, store: [".$niveled."] },
											]
										}]
									},
										{ xtype: 'combo',     fieldLabel: 'Profesion u Ocupacion', labelWidth: 150, name: 'profes',  width:350, store: [".$profes."] },
									]
								},{
									title: 'Variables',
									autoScroll:true,
									defaults:{anchor:'-20'},
									items:[
										{ xtype: 'textfield',   fieldLabel: '".$this->datasis->traevalor('NOMVARI1')."', labelWidth:100, name: 'vari1', allowBlank: true, width: 200 },
										{ xtype: 'textfield',   fieldLabel: '".$this->datasis->traevalor('NOMVARI2')."', labelWidth:100, name: 'vari2', allowBlank: true, width: 200 },
										{ xtype: 'textfield',   fieldLabel: '".$this->datasis->traevalor('NOMVARI3')."', labelWidth:100, name: 'vari3', allowBlank: true, width: 200 },
										{ xtype: 'textfield',   fieldLabel: '".$this->datasis->traevalor('NOMVARI4')."', labelWidth:100, name: 'vari4', allowBlank: true, width: 200 },
										{ xtype: 'datefield',   fieldLabel: '".$this->datasis->traevalor('NOMVARI5')."', labelWidth:100, name: 'vari5', width: 200, format: 'd/m/Y', submitFormat: 'Y-m-d' },
										{ xtype: 'textfield',   fieldLabel: '".$this->datasis->traevalor('NOMVARI6')."', labelWidth:100, name: 'vari6', allowBlank: true, width: 200 },
									]
								}]
							}
						], 
						dockedItems: [
							{ xtype: 'toolbar', dock: 'bottom', ui: 'footer', 
							items: ['->', 
								{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',  scope: this, handler: this.onClose },
								{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
							]
						}]
					});
					this.callParent();
				},
				setActiveRecord: function(record){
					this.activeRecord = record;
				},
				onSave: function(){
					var form = this.getForm();
					if (!registro) {
						if (form.isValid()) {
							storePers.insert(0, form.getValues());
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					} else {
						var active = win.activeRecord;
						if (!active) {
							Ext.Msg.Alert('Registro Inactivo ');
							return;
						}
						if (form.isValid()) {
							form.updateRecord(active);
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					}
					form.reset();
					this.onReset();
				},
				onReset: function(){
					this.setActiveRecord(null);
					storePers.load();
					//Hide Windows 
					win.hide();
				},
				onClose: function(){
					var form = this.getForm();
					form.reset();
					this.onReset();
				}
			});

			win = Ext.widget('window', {
				title: '',
				losable: false,
				closeAction: 'destroy',
				width: 650,
				height: 470,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;

						if (registro) {
							form.loadRecord(registro);
							form.findField('codigo').setReadOnly(true);
						} else {
							form.findField('codigo').setReadOnly(false);
						}
					}
				}
			});
		}
		win.show();
	}

	//Filters
	var filters = {
		ftype: 'filters',
		// encode and local configuration options defined previously for easier reuse
		encode: 'json', // json encode the filter query
		local: false
	};    

	// Create Grid 
	Ext.define('PersGrid', {
		extend: 'Ext.grid.Panel',
		alias: 'widget.writergrid',
		store: storePers,
		initComponent: function(){
			Ext.apply(this, {
				iconCls: 'icon-grid',
				frame: true,
				dockedItems: [{
					xtype: 'toolbar',
					items: [
						{iconCls: 'icon-add',    text: 'Agregar',                                     scope: this, handler: this.onAddClick   },
						{iconCls: 'icon-update', text: 'Modificar', disabled: true, itemId: 'update', scope: this, handler: this.onUpdateClick},
						{iconCls: 'icon-delete', text: 'Eliminar',  disabled: true, itemId: 'delete', scope: this, handler: this.onDeleteClick }
					]
				}],
				columns: colPers,
				// paging bar on the bottom
				bbar: Ext.create('Ext.PagingToolbar', {
					store: storePers,
					displayInfo: true,
					displayMsg: 'Pag No. {0} - Registros {1} de {2}',
					emptyMsg: \"No se encontraron Registros.\"
				})
			});
			this.callParent();
			this.getSelectionModel().on('selectionchange', this.onSelectChange, this);
		},
		features: [{ ftype: 'grouping', groupHeaderTpl: '{name} ' }, filters],
		onSelectChange: function(selModel, selections){
			this.down('#delete').setDisabled(selections.length === 0);
			this.down('#update').setDisabled(selections.length === 0);
			},
		
		onUpdateClick: function(){
			var selection = this.getView().getSelectionModel().getSelection()[0];
				if (selection) {
					registro = selection;
					showContactForm();
				}
			},
		onDeleteClick: function() {
			var selection = this.getView().getSelectionModel().getSelection()[0];
			Ext.MessageBox.show({
				title: 'Confirme', 
				msg: 'Esta seguro?', 
				buttons: Ext.MessageBox.YESNO, 
				fn: function(btn){ 
					if (btn == 'yes') { 
						if (selection) {
							storePers.remove(selection);
						}
						storePers.load();
					} 
				}, 
				icon: Ext.MessageBox.QUESTION 
			});  
		},
		onAddClick: function(){
			registro = null;
			showContactForm();
			storePers.load();
		}
	});



/*
	//Main Container
	var main = Ext.create('Ext.container.Container', {
		padding: '0 0 0 0',
		width: '100%',
		height: 500,
		renderTo: document.body,
		layout: {
			type: 'vbox',
			align: 'center'
		},
		items: [
			{
				xtype: 'panel',
				preventHeader: true,
				collapsible : true,
				html: '".$encabeza."',
				title: 'Busqueda Avanzada',
				width: '98%',
				layout: 'fit',
				viewConfig: { forceFit: true },
				flex: 1
			},
			{
				itemId: 'grid',
				xtype: 'writergrid',
				title: 'Trabajadores',
				width: '98%',
				align: 'center',
				flex: 9,
				store: storePers
			}]
	});
	Ext.EventManager.onWindowResize(main.doLayout, main);
	*/


	Ext.create('Ext.Viewport', {
		layout: {type: 'border',padding: 5},
		defaults: { split: true	},
		items: [
			{
				region: 'north',
				preventHeader: true,
				height: 40,
				minHeight: 40,
				html: '".$encabeza."'
			},{
				region: 'center',
				itemId: 'grid',
				xtype: 'writergrid',
				title: 'Trabajadores',
				width: '98%',
				align: 'center',
				//flex: 9,
				//store: storePers

				//layout: 'border',
				//border: false,
				//items: PersGrid
				//html: 'meco'
			}
		]
	});





	storePers.load({ params: { start:0, limit: 30}});
});

</script>
";
		return $script;	
	}
}

?>