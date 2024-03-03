<?php

namespace HexMakina\Hopper;

class Request
{
    private $alto_match;
    private $query_parameters;
    private $params;

    public function __construct(array $match)
    {
        $this->alto_match = $match;
        $this->query_parameters = $_GET;
        $this->params = array_merge($this->alto_match['params'], $this->query_parameters);
    }

    // DEPRECATED
    public function target()
    {
        return $this->alto_match['target'];
    }

    public function name()
    {
        return $this->alto_match['name'];
    }

    // @return array of all GET params if $name is null
    // @return null if $name is not an index
    // @return return urldecoded string
    public function params($name = null)
    {
        if(is_null($name)){
            return $this->params;
        }

        if (!isset($this->params[$name])) {
            return null;
        }

        if (is_string($this->params[$name])) {
            return urldecode($this->params[$name]);
        }

        return $this->params[$name];
    }

    public function submitted($name = null)
    {
        if(is_null($name)){
            return $_POST;
        }
        
        return $_POST[$name] ?? null;
    }

    public function payload()
    {
        return file_get_contents('php://input');
    }
    
}
