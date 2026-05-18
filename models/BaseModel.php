<?php

require_once 'config/database.php';

class BaseModel {

    protected $db;

    public function __construct() {

        $database = new Database();
        $this->db = $database->connect();
    }
}