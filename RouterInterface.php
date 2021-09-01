<?php

namespace HexMakina\Hopper;

interface RouterInterface
{
    const REQUEST_GET = 'GET';
    const REQUEST_POST = 'POST';
    const ROUTE_HOME_NAME = 'home';

    public function route_exists($route): bool;

    public function match($requestUrl = null, $requestMethod = null);

  // generates URL for href and location
    public function prehop($route, $route_params = []);

  // heads to another location
    public function hop($route = null, $route_params = []);

  // do you GET it ?
    public function requests(): bool;

  // have you POSTed it ?
    public function submits(): bool;
    public function submitted($param_name = null);

    public function send_file($file_path);


    public function web_root(): string;
    // public function file_root(): string;

    public function mapHomeRoute($route);

    public function setFilePath($setter);
    public function setBasePath($setter);

    public function filePath($setter);
    public function basePath($setter);

    // public function set_file_root($setter);

    public function target_controller();
    public function target_method();
    public function name();
}
