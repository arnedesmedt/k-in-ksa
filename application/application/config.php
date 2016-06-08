<?php

namespace Application;

require_once __DIR__."/../../fw/application/config.php";


use FW\Config as FW_Config;

/**
* Config
*/
class Config extends FW_Config
{
    protected function init_parameters()
    {
        parent::init_parameters();

        //ROUTER PARAMETERS
        $this->use_environment = false;
        
        //DATABASE PARAMETERS
        // $this->db_host = "k-in-ksa.be.mysql";
        // $this->db_username = "k_in_ksa_be";
        // $this->db_password = "9vnuzGQ3";
        // $this->db_name = "k_in_ksa_be";

        $this->db_host = "localhost";
        $this->db_username = "k_in_ksa_be";
        $this->db_password = "9vnuzGQ3";
        $this->db_name = "k_in_ksa_be";

        $this->action_list[] = "login";
    }
}