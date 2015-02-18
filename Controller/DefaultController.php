<?php

namespace xrow\bootstrapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;

class DefaultController extends Controller
{
    public function topMenuAction()
    {
        $rootLocationId = $this->getConfigResolver()->getParameter( 'content.tree_root.location_id' );
        $searchService = $this->getRepository()->getSearchService();
        $query = new Query();
        $query->criterion = new ParentLocationId($rootLocationId);
        $list = $searchService->findContent($query);
        return $this->render('xrowbootstrapBundle:parts:top_menu.html.twig',
            array( 'list' => $list) );
    }
}
