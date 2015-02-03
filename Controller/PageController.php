<?php

namespace xrow\bootstrapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRendPageclass;


class PageController extends Controller
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
        return $this->render('xrowbootstrapBundle:Default:test_html.html.twig',array('menu' => $menu));
    }

    public function bootstrapAction()
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
        return $this->render('xrowbootstrapBundle:Default:test_bootstrap.html.twig',array('menu' => $menu));
    }

    public function wuvAction()
    {
        return $this->render('xrowbootstrapBundle:Default:test_elemente_wuv.html.twig');
    }
    
    public function wuv_homeAction()
    {
        return $this->render('xrowbootstrapBundle:Default:test_home_wuv.html.twig');
    }
}


