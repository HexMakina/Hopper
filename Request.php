<?php

/**
* huppel konijntje huppel and wiebel
* Hommage to Grace Hopper, programmer & expert in *litteral* duck taping
***/

namespace HexMakina\Hopper;

class Request
{
    private $alto_match;
    private Response $target;

    public function __construct(array $match)
    {
        $this->alto_match = $match;
        $this->target = new Response($this);
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
        $params = $this->alto_match['params'] ?? [];

        if(is_null($name)){
            return $params;
        }

        if (!isset($params[$name])) {
            return null;
        }

        if (is_string($params[$name])) {
            return urldecode($params[$name]);
        }
        return $params[$name];
    }

    public function submitted($name = null)
    {
        if(is_null($name)){
            return $_POST;
        }
        
        return $_POST[$name] ?? null;
    }
}
