<?php

namespace xrow\bootstrapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class RemoteController extends Controller
{
    /**
     * Replace body content with template "xrowbootstrapBundle:full:remotecontent.html.twig": placeholder <!--CONTENT-->
     * Replace all href, src, action and maybe content paths with absolute paths
     * 
     * @Route("/remote/content/{viewType}/{locationId}", requirements={"viewType" = "\w+", "locationId" = "\d+"}, defaults={"viewType" = "full"})
     */
    public function getContentAction(Request $request, $viewType, $locationId)
    {
        $host = $request->getHost();
        $repository = $this->container->get('ezpublish.api.repository');
        try {
            $location = $repository->getLocationService()->loadLocation($locationId);
        }
        catch (UnauthorizedException $e) {
            throw new AccessDeniedException('Access Denied', $e);
        }
        catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
        if ($location->invisible) {
            throw new NotFoundHttpException("Location #$locationId cannot be displayed as it is flagged as invisible.");
        }
        $locationViewMatcher = $this->container->get('ezpublish.location_view.matcher_factory');
        $match = $locationViewMatcher->match($location, $viewType);
        if (isset($match['template']) || isset($match['controller'])) {
            $templateIdentifier = isset($match['template']) ? $match['template'] : $match['controller'];
            if (strpos($templateIdentifier, '::') !== false)
                $templateIdentifierArray = explode('::', $templateIdentifier);
            elseif (strpos($templateIdentifier, ':') !== false)
                $templateIdentifierArray = explode(':', $templateIdentifier);
            if (isset($templateIdentifierArray[0])) {
                $designBundle = $templateIdentifierArray[0];
                $response = $this->render('xrowbootstrapBundle:full:remotecontent.html.twig', array('designBundle' => $designBundle));
                $content = $response->getContent();
                $content = preg_replace('#(href|src|action)="(?!(|http:|https:)//)([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#', '$1="//' . $host . '$2$3"', $content);
                $content = preg_replace('#url\((?!\s*[\'"]?(?:https?:)?//)\s*([\'"])?#', "url($1//{$host}", $content);
                if (preg_match('#<meta name="msapplication-TileImage" content="(?!(|http:|https:)//)([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#', $content)) {
                    $content = preg_replace('#<meta name="msapplication-TileImage" content="(?!(|http:|https:)//)([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#', '<meta name="msapplication-TileImage" content="//' . $host . '$2$3"', $content);
                }
                $response->setContent($content);
                return $response;
            }
        }
        else {
            $contentType = $repository->getContentTypeService()->loadContentType($location->contentInfo->contentTypeId)->identifier;
            return new Response('Please set a template or controller for contentType "'.$contentType.'" in your YAML.', 500);
        }
    }
}