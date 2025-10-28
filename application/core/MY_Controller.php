<?php
/**
 * -----------------------------IMPORTANT-------------------------------
 * Programmer should NOT change or add any code without having a better
 * understanding how MY_CONTROLLER and Its methods been used
 * ---------------------------------------------------------------------
 *
 * My_Controller will be used for all the CRUD operations in the system.
 *
 * All the other models should be extend form My_Model
 * Most of the common operations been written in the My_Model so that
 * programmer can easily call methods in My_Model Class for all most
 * all Database Communication and minimize the coding in their projects.
 *
 */
class MY_Controller extends CI_Controller
{
    function __construct(){
        parent::__construct();

        $this->load->helper('form');
        $this->load->helper('date');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('user_agent');

	}
}
