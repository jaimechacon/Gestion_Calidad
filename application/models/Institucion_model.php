<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Institucion_model extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
		parent::__construct();
	}

	public function listarInstitucionesUsu($id_usuario)
	{
		$query = $this->db->query('CALL `institucionminsal`.`listarInstitucionesUsu`('.$id_usuario.');');
		return $query->result_array();
	}

}