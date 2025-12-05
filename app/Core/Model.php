<?php

namespace App\Core;

use App\Config\Database;
use PDO;

abstract class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}
