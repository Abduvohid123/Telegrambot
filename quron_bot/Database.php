<?php

class Database
{

    public $conn;

    function connect(){
        $this->conn=mysqli_connect('localhost','user','password','quran_bot');
    }
    public function __construct()
    {
        $this->connect();
    }

    public  function  create(){

    }
}