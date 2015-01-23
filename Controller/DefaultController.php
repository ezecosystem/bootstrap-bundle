<?php

namespace xrow\bootstrapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $factory = new MenuFactory();
        $menu = $factory->createItem('Grünzeug Menü');
        $menu->addChild('Home', array('uri' => '#'));
        $menu->addChild('Bäume', array('uri' => '#'));
        $menu->addChild('Blumen', array('uri' => '#'));
        $menu->addChild('Kräuter', array('uri' => '#'));
        $menu['Kräuter']->addChild('Suppengrün', array('uri' => '#'));
        $menu['Kräuter']->addChild('Edelwurz', array('uri' => '#'));
        $menu['Kräuter']->addChild('Snuff', array('uri' => '#'));
        #return $this->render('xrowbootstrapBundle:Default:index.html.twig');
        return $this->render('xrowbootstrapBundle:Default:index.html.twig',array('menu' => $menu));
    }
}
