<?php

namespace xrow\bootstrapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RemoteController extends Controller
{
    /**
     * @Route("/remote/content/{viewType}/{locationId}", requirements={"viewType" = "\w+", "locationId" = "\d+"}, defaults={"viewType" = "full"})
     */
    public function getContentAction(Request $request, $viewType, $locationId)
    {
        $response = $this->get( 'ez_content' )->viewLocation(
            $locationId,
            $viewType
        );

        return $response;
    }
}