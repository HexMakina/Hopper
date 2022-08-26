<?php

/**
* huppel konijntje huppel and wiebel
* Hommage to Grace Hopper, programmer & expert in *litteral* duck taping
***/

namespace HexMakina\Hopper;

class Hopper extends \AltoRouter implements \HexMakina\BlackBox\RouterInterface
{
    private $match = null;
    private $file_root = null;

    public function __construct($route_home, $web_base, $file_root)
    {
      $this->mapHomeRoute($route_home);
      $this->basePath($web_base);
      $this->filePath($file_root);
    }

  //----------------------------------------------------------- INITIALISATION

    public function mapHomeRoute($route)
    {
        $this->map(self::REQUEST_GET, '', $route, self::ROUTE_HOME_NAME);
    }

    public function __debugInfo(): array
    {
        $dbg = get_object_vars($this);
        $dbg['routes'] = count($dbg['routes']);
        $dbg['namedRoutes'] = count($dbg['namedRoutes']);
        unset($dbg['matchTypes']);
        return $dbg;
    }
  // -- MATCHING REQUESTS
    public function match($requestUrl = null, $requestMethod = null)
    {
        $this->match = parent::match($requestUrl, $requestMethod);

        if ($this->match === false) {
            throw new RouterException('ROUTE_MATCH_FALSE');
        }

        $res = explode('::', $this->target());

        if ($res === false || !isset($res[1]) || isset($res[2])) {
            throw new RouterException('INVALID_TARGET_FORMAT');
        }

        $this->match['target_controller'] = $res[0];
        $this->match['target_method'] = $res[1];

        return [$res[0], $res[1]];
    }

    public function params($param_name = null)
    {
        return $this->extractFrom($this->match['params'] ?? [], $param_name);
    }

    public function submitted($param_name = null)
    {
        return $this->extractFrom($_POST, $param_name);
    }



    public function target()
    {
        return $this->match['target'];
    }

    public function targetController()
    {
        return $this->match['target_controller'];
    }

    public function targetMethod()
    {
        return $this->match['target_method'];
    }

    public function name()
    {
        return $this->match['name'];
    }

  // -- ROUTING TOOLS
    public function routeExists($route): bool
    {
        return isset($this->namedRoutes[$route]);
    }

    public function namedRoutes()
    {
        return $this->namedRoutes;
    }

  /* Generates HYPertext reference
   * @param route_name string  requires
   *  - a valid AltoRouter route name
   *  - OR a Descendant of Model
   * @route_params requires
   *  - an assoc_array of url params (strongly AltoRouter-based)
   * returns: something to put in a href="", action="" or header('Location:');
   */
    public function hyp($route, $route_params = [])
    {
        try {
            $url = $this->generate($route, $route_params);
        } catch (\Exception $e) {
            $url = $this->hyp(self::ROUTE_HOME_NAME);
        }

        return $url;
    }



  /*
   * @params $route is
   *    - empty: default is ROUTE_HOME_NAME
   *    - an existing route name: make url with optional [$route_params])
   *    - a url, go there
   * @params $route_params, assoc_data for url creation (i:id, a:format, ..)
   */
    public function hop($route = null, $route_params = [])
    {
        $url = null;

        if (is_null($route)) {
            $url = $this->hyp(self::ROUTE_HOME_NAME, $route_params);
        } elseif (is_string($route) && $this->routeExists($route)) {
            $url = $this->hyp($route, $route_params);
        } else {
            $url = $route;
        }

        $this->hopURL($url);
    }

    public function stay($url = null)
    {
        return $url ?? $_SERVER['REQUEST_URI'];
    }

  // hops back to previous page (referer()), or home if no referer
    public function hopBack()
    {
        if (!is_null($back = $this->referer())) {
            $this->hopURL($back);
        }

        $this->hop();
    }

    public function hopURL($url)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 01 Jan 1970 00:00:00 GMT');
        header('Location: ' . $url);
        exit();
    }

  // returns full URL of the refering URL
  // returns null if same as current URL (prevents endless redirection loop)
    public function referer()
    {
        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != $this->webHost() . $_SERVER['REQUEST_URI']) {
            return $_SERVER['HTTP_REFERER'];
        }

        return null;
    }

    public function sendFile($file_path)
    {
        if (!file_exists($file_path)) {
            throw new RouterException('SENDING_NON_EXISTING_FILE');
        }

        $file_name = basename($file_path);

      //Get file type and set it as Content Type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        header('Content-Type: ' . finfo_file($finfo, $file_path));

        finfo_close($finfo);

      //Use Content-Disposition: attachment to specify the filename
        header('Content-Disposition: attachment; filename=' . $file_name);

      //No cache
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

      //Define file size
        header('Content-Length: ' . filesize($file_path));

        ob_clean();
        flush();
        readfile($file_path);
        // die; // might be useless after all
    }

  // -- PROCESSING REQUESTS
    public function requests(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === self::REQUEST_GET;
    }

    public function submits(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === self::REQUEST_POST;
    }

    public function webHost(): string
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }

    public function webRoot(): string
    {
        return $this->webHost() . $this->basePath();
    }

    // return web base
    public function basePath($setter = null): string
    {
        if (!is_null($setter)) {
            $this->basePath = $setter;
        }

        return $this->basePath ?? '';
    }

    // returns root filepath for project
    // default out of vendor/hexmakina/Hopper
    public function filePath($setter = null): string
    {
        if (!is_null($setter)) {
            $this->file_root = realpath($setter) . '/';
        }

        return $this->file_root ?? __DIR__ . '/../../';
    }


    private function extractFrom($dat_ass, $key = null)
    {

      // $key is null, returns $dat_ass or empty array
        if (is_null($key)) {
            return $dat_ass ?? [];
        }

      // $dat_ass[$key] not set, returns null
        if (!isset($dat_ass[$key])) {
            return null;
        }

      // $dat_ass[$key] is a string, returns decoded value
        if (is_string($dat_ass[$key])) {
            return urldecode($dat_ass[$key]);
        }

      // $dat_ass[$key] is not a string, return match[$key]
        return $dat_ass[$key];
    }
}
