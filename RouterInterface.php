<?php

namespace HexMakina\Hopper;

interface RouterInterface
{
    const REQUEST_GET = 'GET';
    const REQUEST_POST = 'POST';
    const ROUTE_HOME_NAME = 'home';

    public function routeExists($route): bool;

    public function match($requestUrl = null, $requestMethod = null);

  // generates URL for href and location
    public function hyp($route, $route_params = []);

  // heads to another location
    public function hop($route = null, $route_params = []);

  // do you GET it ?
    public function requests(): bool;

  // have you POSTed it ?
    public function submits(): bool;
    public function submitted($param_name = null);

    public function sendFile($file_path);


    public function webRoot(): string;
    // public function file_root(): string;

    public function mapHomeRoute($route);

    public function setFilePath($setter);
    public function setBasePath($setter);

    public function filePath($setter = null): string;
    public function basePath($setter = null): string;

    // public function set_file_root($setter);

    public function targetController();
    public function targetMethod();
    public function name();
}
