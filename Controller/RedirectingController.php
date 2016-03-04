<?php

namespace xrow\bootstrapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectingController extends Controller
{
    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        // For not knowen routes. Without this we get a loop.
        $checkPathInfo = str_replace($pathInfo, rtrim($pathInfo, ' /'), $pathInfo);
        $router = $this->container->get('router');
        $found = false;
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            if ($checkPathInfo == $route->getPath()) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new NotFoundHttpException();
        }
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
        return $this->redirect($url, 301);
    }
}