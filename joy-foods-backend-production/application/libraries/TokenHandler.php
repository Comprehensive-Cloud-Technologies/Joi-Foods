<?php
require APPPATH . '/libraries/JWT.php';
class TokenHandler
{
    //////////The function generate token/////////////
    private $key;

    public function __construct()
    {
        $this->key = $_ENV['JWT_SECRET'];
    }

    public function GenerateToken($data)
    {
        $jwt = JWT::encode($data, $this->key);
        return $jwt;
    }

    //////This function decode the token////////////////////
    public function DecodeToken($token)
    {
        $decoded = JWT::decode($token, $this->key, array('HS256'));
        $decodedData = $decoded;
        return $decodedData;
    }
}
