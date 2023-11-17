<?php


namespace HexMakina\Hopper;

class Target
{
    private string $controller;
    private string $method;

    public function __construct(Request $request)
    {
      $res = explode('::', $request->target());

      // if explode() returned false
      // or if the array doesn't have a second element (the method)
      // or if the array has more than 2 elements
      if ($res === false || !isset($res[1]) || isset($res[2])) {
          throw new RouterException('INVALID_TARGET_FORMAT');
      }

      $this->controller = $res[0];
      $this->method = $res[1];
    }

    public function controller()
    {
        return $this->controller;
    }

    public function method()
    {
        return $this->method;
    }
}
?>