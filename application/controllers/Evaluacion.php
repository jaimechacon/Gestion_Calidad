<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Evaluacion extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('usuario_model');
		$this->load->model('evaluacion_model');
		$this->load->model('grabacion_model');
		$this->load->model('campania_model');
		$this->load->library('ftp');
	}

	public function index()
	{
		$usuario = $this->session->userdata();
		if($usuario){
			$this->load->view('temp/header');
			$this->load->view('temp/menu', $usuario);
			$this->load->view('evaluarUsuarios', $usuario);
			$this->load->view('temp/footer');
		}else
		{
			//$data['message'] = 'Verifique su email y contrase&ntilde;a.';
			redirect('Inicio');
		}
	}

	public function evaluarUsuarios()
	{
		$usuario = $this->session->userdata();		
		if($usuario){
			$rango = 2;

			// inicio proceso de copiado a tabla temporal usuarios grabaciones
			$usuariosGrabaciones = $this->grabacion_model->obtenerUsuariosGrabacion();
			$seEliminanUsuarios = $this->evaluacion_model->truncarUsuariosGrabacion();

			//var_dump($usuariosGrabaciones);

			mysqli_next_result($this->db->conn_id);

			for ($e=0; $e < count($usuariosGrabaciones); $e++) {
				$usuariosEAC = explode(",", $usuariosGrabaciones[$e]["users"]);
				$u_cod_campania = $usuariosGrabaciones[$e]["tipo"];

				//var_dump($u_cod_campania);
				//var_dump($usuariosEAC);

				for ($i=0; $i < count($usuariosEAC); $i++) {

					$u_cod_usuario = $usuariosEAC[$i];
					$seAgregaUsuario = $this->evaluacion_model->agregarUsuarioGrabacion($u_cod_campania, $u_cod_usuario);
					mysqli_next_result($this->db->conn_id);
				}
			}
			// fin proceso de copiado a tabla temporal usuarios grabaciones
			//$cantCampanias = $this->campania_model->listarCampaniasUsu($usuario["id_usuario"]);
			
			$ciclos = $this->evaluacion_model->obtenerCiclos("null");
			$usuario['ciclos'] = $ciclos[0];

			mysqli_next_result($this->db->conn_id);
			$fases = $this->evaluacion_model->obtenerFases("null");
			$usuario['fases'] = $fases;

			mysqli_next_result($this->db->conn_id);
			$evaluaciones = $this->evaluacion_model->listar_evaluaciones($usuario["id_usuario"], $rango, $ciclos[0]['ciclo_actual'], $usuario["id_usuario"], "null");

			//var_dump(!isset($evaluaciones['resultado']));
			

			//var_dump($evaluaciones);
			if(isset($evaluaciones[0]['resultado']) && $evaluaciones[0]['resultado'] == "1")
			{
				//var_dump($evaluaciones);
				$usuario['evaluaciones'] = $evaluaciones;

				mysqli_next_result($this->db->conn_id);
				$usuariosAnalistas = $this->usuario_model->obtenerUsuariosEvaluadores($usuario["id_usuario"]);
				if($usuariosAnalistas)
					$usuario["usuariosAnalistas"] = $usuariosAnalistas;
				//var_dump($usuariosAnalistas);

				mysqli_next_result($this->db->conn_id);
				$empresas =  $this->usuario_model->obtenerEmpresasUsu($usuario["id_usuario"]);
				if($empresas)
				{
					$usuario['empresas'] = $empresas;
				}
			}

			$usuario['controller'] = 'evaluacion';
			$this->load->view('temp/header');
			$this->load->view('temp/menu', $usuario);
			$this->load->view('evaluarUsuarios', $usuario);
			$this->load->view('temp/footer', $usuario);			
		}else
		{
			$data['message'] = 'Verifique su email y contrase&ntilde;a.';
			redirect('Login');
		}
	}

	public function filtros()
	{
		if($this->input->post('data'))
		{
			echo $this->input->post('data');
		}
	}

	public function agregarEvaluacion()
	{
		$usuario = $this->session->userdata();
		$usuario['controller'] = 'evaluacion';

		/*$config['hostname'] = '192.168.158.5';
		$config['username'] = 'client1';
		$config['password'] = 'neopass';
		$config['debug']    = TRUE;
		$config['port']     = 21;
		$config['passive']  = FALSE;*/

		//mysqli_next_result($this->db->conn_id);
		//$resultado = $this->ftp->connect($config);
		//echo $resultado;

		//$list = $this->ftp->list_files('/MONITOREO/');
		//print_r($list);
		//$this->ftp->upload('/local/path/to/myfile.html', '/public_html/myfile.html', 'ascii', 0775);
		//$this->ftp->download('/MONITOREO/930096559-1151769-20180810190847.mp3', '/grabaciones/MP3.mp3', 'ascii');
		//$url = (base_url().'grabaciones/MP3.mp3');
		//echo $url;
		//$this->ftp->upload($url, '/MONITOREO/931700224-1154462-20180814132148.mp3', 'ascii', 0775);
		//$this->ftp->download('/MONITOREO/931748754-1150859-20180809203635.mp3', 'grabaciones/MP3.mp3');
		//$this->ftp->close();
		//mysqli_next_result($this->db->conn_id);
		
		if($usuario){
			//mysqli_next_result($this->db->conn_id);
			//mysqli_next_result($this->db->conn_id);
			if($this->input->GET('idCamp') && $this->input->GET('idEAC'))
			{
				//mysqli_next_result($this->db->conn_id);
				$idCampania = $this->input->GET('idCamp');
				$codCampania = $this->input->GET('codCamp');
				$idEAC = $this->input->GET('idEAC');
				$idUsuResp = $this->input->GET('idUsuResp');

				$usuario['idCampania'] = $idCampania;
				$usuario['codCampania'] = $codCampania;
				$usuario['idEAC'] = $idEAC;

				$usuarioResp = $this->usuario_model->obtenerUsuario($idUsuResp);
				$usuario['u_nombres_resp'] = $usuarioResp[0]['u_nombres'];
				$usuario['u_apellidos_resp'] = $usuarioResp[0]['u_apellidos'];
				$usuario['idUsuResp'] = $idUsuResp;


				mysqli_next_result($this->db->conn_id);
				$pauta =  $this->evaluacion_model->obtenerPlantillaEAC($idEAC, $idCampania);
				$usuario['pauta'] = $pauta;
				$temp = array_unique(array_column($pauta, 'cat_nombre'));
				$cat_pauta = array_intersect_key($pauta, $temp);
				$usuario['cat_pauta'] = $cat_pauta;
				

				$grabaciones = $this->grabacion_model->listarGrabacionesUsu($idEAC, $codCampania);
				//var_dump($grabaciones);
				if(sizeof($grabaciones) > 0)
					$usuario['grabacion'] = $grabaciones[0];

				$ruta = base_url().'grabaciones/';
				$usuario['ruta'] = $ruta;
				$usuario['idEAC'] = $idEAC;
			}else{
				$campanias = $this->usuario_model->listarCampaniasUsu($usuario["id_usuario"]);
				$usuario['campanias'] = $campanias;
			}
			
			$this->load->view('temp/header');
			$this->load->view('temp/menu', $usuario);
			$this->load->view('agregarEvaluacion', $usuario);
			$this->load->view('temp/footer', $usuario);
		}else
		{
			$data['message'] = 'Verifique su email y contrase&ntilde;a.';
			redirect('Login');
		}
	}

	public function guardarEvaluacion()
	{
		$usuario = $this->session->userdata();
		if($usuario){
			if(!is_null($this->input->POST('idEAC')))
			{
				$idEAC = trim($this->input->POST('idEAC'));
				$idCampania = "null";
				$observaciones = "";

				$idLlamada = "";
				$duracionSegundos = "";
				$duracionMinutos = "";
				$nombreGrabacion = "";
				$fechaGrabacion = "";
				$duracionMinutos = "";
				$idUsuResp = "null";

				if(!is_null($this->input->POST('idUsuResp')))
					$idUsuResp = trim($this->input->POST('idUsuResp'));
				
				$accion = 'agregado';
				if(!is_null($this->input->POST('observacionesEvaluacion')))
					$observacionesEvaluacion = trim($this->input->POST('observacionesEvaluacion'));

				if(!is_null($this->input->POST('idLlamada')))
					$idLlamada = trim($this->input->POST('idLlamada'));

				if(!is_null($this->input->POST('duracionSegundos')))
					$duracionSegundos = trim($this->input->POST('duracionSegundos'));

				if(!is_null($this->input->POST('duracionMinutos')))
					$duracionMinutos = trim($this->input->POST('duracionMinutos'));
				
				if(!is_null($this->input->POST('nombreGrabacion')))
					$nombreGrabacion = trim($this->input->POST('nombreGrabacion'));

				if(!is_null($this->input->POST('fechaGrabacion')))
					$fechaGrabacion = trim($this->input->POST('fechaGrabacion'));

				if(!is_null($this->input->POST('idCampania')))
					$idCampania = trim($this->input->POST('idCampania'));
				
				$idEvaluacion = 'null';
				$preguntas[] = array();
				unset($preguntas);

				if(!is_null($this->input->POST('preguntasEvaluacion')))
					$preguntas = $this->input->POST('preguntasEvaluacion');

				
				//echo ($this->input->POST('preguntas'));
				/*if(!is_null($this->input->POST('idEvaluacion')) && is_numeric($this->input->POST('idEvaluacion')))
					$idEvaluacion = $this->input->POST('idEvaluacion');*/

				if(!is_null($this->input->POST('idEvaluacion')) && is_numeric($this->input->POST('idEvaluacion')) && (int)$this->input->POST('idEvaluacion') != 1)
				{
					$idEvaluacion = (int)$this->input->POST('idEvaluacion');
					$accion = 'modificado';
				}
					



				$respuesta = 0;
				$cantPre = 0;
				$mensaje = '';
				$cantRes = 0;

				//var_dump($idPlantilla, $nombre, $abreviacion, $observaciones);
				//mysqli_next_result($this->db->conn_id);

				$resultado = $this->evaluacion_model->guardarEvaluacion($idEvaluacion, $idEAC, $idCampania, $idLlamada, $nombreGrabacion, $fechaGrabacion, $duracionSegundos, $duracionMinutos, $observacionesEvaluacion, $idUsuResp, $usuario["id_usuario"]);
				//var_dump($resultado);

				if($resultado[0] > 0)
				{
					//var_dump($resultado[0]);
					if($resultado[0]['idEvaluacion'])
					{
						if($idEvaluacion == 'null')
							$idEvaluacion = (int)$resultado[0]['idEvaluacion'];

						//var_dump($resultado[0]);
						$respuesta = 1;
						$mensaje = 'Se ha '.$accion.' la evaluaci&oacute;n exitosamente.';

						//echo $resultado[0]['idEvaluacion'];
						if(isset($preguntas))
						{
							$cantPre = sizeof($preguntas);
							if($cantPre > 0)
							{
								//var_dump($cantPre);
								//mysqli_next_result($this->db->conn_id);
								//$resultadoAPE = $this->plantilla_model->eliminarCatPrePlantilla($idPlantilla, $usuario["id_usuario"]);

								//if($resultadoAE > 0)
								//{
								foreach ($preguntas as $pregunta) {
									
									$pregunta = explode(",", $pregunta);
									//var_dump($pregunta);
									if(is_numeric($pregunta[0]))
									{
										$idplacatpre = (int)$pregunta[0];
										$respuesta = $pregunta[1];
										//var_dump($idEvaluacion.' - '.$idplacatpre.' - '.$respuesta.' - '.$usuario["id_usuario"]);

										mysqli_next_result($this->db->conn_id);
										$resultadoARE = $this->evaluacion_model->guardarRespuestaPreguntaEvaluacion($idEvaluacion, $idplacatpre, $respuesta, $usuario["id_usuario"]);
										//var_dump($resultadoARE);
										if($resultadoARE > 0)
										{
											$cantRes ++;
										}else{
											$respuesta = 0;
										}	
									}
								}

								$mensaje = $mensaje.' Se han insertado '.$cantRes.' respuestas a la Evaluacion.';
								//}
							}
						}
					}
				}else
				{
					if($resultado === 0)
					{
						$mensaje = 'Ha ocurrido un error al modificar el plantilla, la plantilla no se encuentra registrada.';
					}
				}
				$data['respuesta'] = $respuesta;
				$data['cantPre'] = $cantPre;
				$data['cantRes'] = $cantRes;
				$data['mensaje'] = $mensaje;
				echo json_encode($data);
			}
		}
	}

	public function modificarEvaluacion()
	{
		$usuario = $this->session->userdata();
			
		

		$config['hostname'] = '192.168.158.5';
		$config['username'] = 'client1';
		$config['password'] = 'neopass';
		$config['debug']    = TRUE;
		$config['port']     = 21;
		$config['passive']  = FALSE;

		//$resultado = $this->ftp->connect($config);
		//echo $resultado;

		//$list = $this->ftp->list_files('/MONITOREO/');
		//print_r($list);
		//$this->ftp->upload('/local/path/to/myfile.html', '/public_html/myfile.html', 'ascii', 0775);
		//$this->ftp->download('/MONITOREO/930096559-1151769-20180810190847.mp3', '/grabaciones/MP3.mp3', 'ascii');
		//$url = (base_url().'grabaciones/MP3.mp3');
		//echo $url;
		//$this->ftp->upload($url, '/MONITOREO/931700224-1154462-20180814132148.mp3', 'ascii', 0775);
		//$this->ftp->download('/MONITOREO/931748754-1150859-20180809203635.mp3', 'grabaciones/MP3.mp3');
		//$this->ftp->close();
		$campanias = $this->usuario_model->listarCampaniasUsu($usuario["id_usuario"]);
		$usuario['campanias'] = $campanias;
		if($usuario){
			//mysqli_next_result($this->db->conn_id);
			//mysqli_next_result($this->db->conn_id);

				mysqli_next_result($this->db->conn_id);
				$idCampania = 2;
				$idEAC = 87;
				$pauta =  $this->evaluacion_model->obtenerPlantillaEAC($idEAC, $idCampania);
				$usuario['pauta'] = $pauta;

			
				$temp = array_unique(array_column($pauta, 'CAT_NOMBRE'));
				$cat_pauta = array_intersect_key($pauta, $temp);
				$usuario['cat_pauta'] = $cat_pauta;			
			
			$this->load->view('temp/header');
			$this->load->view('temp/menu', $usuario);
			$this->load->view('modificarEvaluacion', $usuario);
			$this->load->view('temp/footer');
		}else
		{
			$data['message'] = 'Verifique su email y contrase&ntilde;a.';
			redirect('Login');
		}
	}

	public function listarGrabacionesUsu(){
		$usuario = $this->session->userdata();
		$grabaciones = array();
		if($usuario){

			if(!is_null($this->input->POST('idEAC')))
			{
				if(!is_null($this->input->POST('codCampania')))
				{
					$idEAC = $this->input->POST('idEAC');
					$codCampania = $this->input->POST('codCampania');
					$grabaciones = $this->grabacion_model->listarGrabacionesUsu($idEAC, $codCampania);
				}
			}
		}
		echo json_encode($grabaciones);
	}

	public function listarEvaluaciones()
	{

		$usuario = $this->session->userdata();
			
		if($usuario)
		{
			$idCampania = "null";
			$idEAC = "null";

			if(!is_null($this->input->GET('idCampania')))
				$idCampania = $this->input->GET('idCampania');
							
			if(!is_null($this->input->GET('idEAC')))
				$idEAC = $this->input->GET('idEAC');

			$idAnalista = 'null';
			$esAnalista = $this->evaluacion_model->esAnalista($usuario["id_usuario"]);
				
			$ruta = base_url().'grabaciones/';
			$usuario['ruta'] = $ruta;
			//var_dump($esAnalista);
			//var_dump(isset($esAnalista[0]));
			//var_dump($esAnalista[0]["es_analista"] == "1");
			if($esAnalista && isset($esAnalista[0]["es_analista"]) && $esAnalista[0]["es_analista"] == "1")
			{
				$idAnalista = $usuario['id_usuario'];
				$usuario["esAnalista"] = "1";
				$usuario["idAnalista"] = $idAnalista;
			}


			mysqli_next_result($this->db->conn_id);
			$analistas = $this->evaluacion_model->obtenerUsuariosRespEvaluaciones($idAnalista, $idCampania, $idEAC);

			$usuario['analistas'] = $analistas;
			$usuario['controller'] = 'evaluacion';
			mysqli_next_result($this->db->conn_id);

			$campanias = $this->usuario_model->listarCampaniasUsu($usuario["id_usuario"]);
			$usuario['campanias'] = $campanias;
			$usuario['id_campania'] = $idCampania;
			$usuario['id_eac'] = $idEAC;
			mysqli_next_result($this->db->conn_id);
			//$eacs = $this->evaluacion_model->obtener_usuarios_eacs();
			
			$eacs = $this->evaluacion_model->obtenerUsuariosEACEvaluaciones($idAnalista, $idCampania, 'null');
			$usuario['eacs'] = $eacs;
			//var_dump($this->evaluacion_model->obtenerUsuariosEACEvaluaciones('null', 'null', 'null'));
			mysqli_next_result($this->db->conn_id);
			$evaluaciones = $this->listaEvaluacionesFiltros($idAnalista, $idCampania, $idEAC, "null", "null");
			$usuario['evaluaciones'] = $evaluaciones;

			$this->load->view('temp/header');
			$this->load->view('temp/menu', $usuario);
			$this->load->view('listarEvaluaciones', $usuario);
			$this->load->view('temp/footer', $usuario);
		}else
		{
			$data['message'] = 'Verifique su email y contrase&ntilde;a.';
			redirect('Login');
		}
	}


	public function listaEvaluacionesFiltros($analistaFiltro, $campaniaFiltro, $eacFiltro, $fechaDesdeIn, $fechaHastaIn)
	{
		$usuario = $this->session->userdata();
		$evaluaciones = [];
		if($usuario)
		{
			$analista = "null";
			$campania = "null";
			$eac = "null";
			$fechaDesde = "null";
			$fechaHasta = "null";

			if(!is_null($analistaFiltro) && $analistaFiltro != "" && $analistaFiltro != -1)
				$analista = $analistaFiltro;

			if(!is_null($campaniaFiltro) && $campaniaFiltro != "" && $campaniaFiltro != -1)
				$campania = $campaniaFiltro;

			if(!is_null($eacFiltro) && $eacFiltro != "" && $eacFiltro != -1)
				$eac = $eacFiltro;

			if(!is_null($eacFiltro) && $eacFiltro != "" && $eacFiltro != -1)
				$eac = $eacFiltro;

			if(!is_null($fechaDesdeIn) && $fechaDesdeIn != "" && $fechaDesdeIn != -1)
				$fechaDesde = $fechaDesdeIn;

			if(!is_null($fechaHastaIn) && $fechaHastaIn != "" && $fechaHastaIn != -1)
				$fechaHasta = $fechaHastaIn;

			$evaluaciones = $this->evaluacion_model->listaEvaluaciones($analista, $campania, $eac, $fechaDesde, $fechaHasta);
		}

		return $evaluaciones;
	}

	public function filtrarEvaluaciones()
	{
		$usuario = $this->session->userdata();
			
		if($usuario)
		{
			$analista = "null";
			$campania = "null";
			$eac = "null";
			$fechaDesde = "null";
			$fechaHasta = "null";
			if (!is_null($this->input->POST('analista')) && $this->input->POST('analista') != "" && $this->input->POST('analista') != -1)
				$analista = $this->input->POST('analista');

			if(!is_null($this->input->POST('campania')) && $this->input->POST('campania') != "" && $this->input->POST('campania') != -1)
				$campania = $this->input->POST('campania');

			if(!is_null($this->input->POST('eac')) && $this->input->POST('eac') != "" && $this->input->POST('eac') != -1)
				$eac = $this->input->POST('eac');

			if(!is_null($this->input->POST('fechaDesde')) && $this->input->POST('fechaDesde') != "" && $this->input->POST('fechaDesde') != -1)
				$fechaDesde = $this->input->POST('fechaDesde');

			if(!is_null($this->input->POST('fechaHasta')) && $this->input->POST('fechaHasta') != "" && $this->input->POST('fechaHasta') != -1)
				$fechaHasta = $this->input->POST('fechaHasta');

			$datos[] = array();
			unset($datos);
			$evaluaciones = $this->listaEvaluacionesFiltros($analista, $campania, $eac, $fechaDesde, $fechaHasta);
			mysqli_next_result($this->db->conn_id);
			$analistas = $this->evaluacion_model->obtenerUsuariosRespEvaluaciones($analista, $campania, $eac);
			mysqli_next_result($this->db->conn_id);
			$campanias = $this->evaluacion_model->obtenerCampaniasEvaluaciones($analista, $campania, $eac);
			mysqli_next_result($this->db->conn_id);
			$eacs = $this->evaluacion_model->obtenerUsuariosEACEvaluaciones($analista, $campania, $eac);
			$datos['evaluaciones'] = $evaluaciones;
			$datos['analistas'] = $analistas;
			$datos['campanias'] = $campanias;
			$datos['eacs'] = $eacs;
		}

		echo json_encode($datos);
	}

	public function obtenerResultadoEvaluacion(){
		$usuario = $this->session->userdata();
		$evaluacion[] = array();
		unset($evaluacion);
		if($usuario)
		{
			$idEvaluacion = null;
			if(!is_null($this->input->POST('idEvaluacion')) && $this->input->POST('idEvaluacion') != "" && $this->input->POST('idEvaluacion') != -1)
			{
				$idEvaluacion = $this->input->POST('idEvaluacion');				
				$pauta =  $this->evaluacion_model->listarPlantillaResultado($idEvaluacion);
				$evaluacion['pauta'] = $pauta;
				$temp = array_unique(array_column($pauta, 'cat_nombre'));
				$cat_pauta[] = array();
				unset($cat_pauta);
				$cont = 0;
				foreach ($temp as $key => $value) {
					$categoria['id_categoria'] = $key;
					$categoria['nombre_categoria'] = $value;
					$cat_pauta[$cont] = $categoria;
					$cont++;
				}
				$evaluacion['cat_pauta'] = $cat_pauta;
			}
		}
		echo json_encode($evaluacion);
	}

	public function listarGestionesUsuario()
	{
		$usuario = $this->session->userdata();
		$gestiones[] = array();
		unset($gestiones);

		if($usuario)
		{
			$rango = "2";
			$idUsuarioAnalista = $usuario["id_usuario"];
			$ciclo = "null";
			$fase = "null";

			if(!is_null($this->input->post('rango')))
				$rango = $this->input->post('rango');

			if(!is_null($this->input->post('idUsuarioAnalista')))
				$idUsuarioAnalista = $this->input->post('idUsuarioAnalista');

			if(!is_null($this->input->post('ciclo')))
				$ciclo = $this->input->post('ciclo');

			if(!is_null($this->input->post('fase')))
				$fase = $this->input->post('fase');

			$gestiones = $this->evaluacion_model->listar_evaluaciones($idUsuarioAnalista, $rango, $ciclo, $usuario["id_usuario"], $fase);
		}
		echo json_encode($gestiones);
	}

	public function obtenerCiclos()
	{
		$usuario = $this->session->userdata();
		$ciclos = [];
		if($usuario)
		{
			$fechaFase = "null";
			if(!is_null($this->input->post('fase')))
				$fechaFase = $this->input->post('fase');
			$ciclos = $this->evaluacion_model->obtenerCiclos($fechaFase);
		}
		echo json_encode($ciclos[0]);
	}


}