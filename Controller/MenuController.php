<?php
namespace xrow\bootstrapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
#use eZ\Bundle\EzPublishCoreBundle\Controller;

class MenuController extends Controller
{
    /**
     * @param mixed|null $currentLocationId
     * @return Response
     */
    public function topMenuAction( $currentLocationId )
    {
        if ( $currentLocationId !== null )
        {
            $location = $this->getLocationService()->loadLocation( $currentLocationId );
            if ( isset( $location->path[2] ) )
            {
                $secondLevelLocationId = $location->path[2];
            }
        }

        $response = new Response;
        $menu = $this->getMenu( 'top' );
        $parameters = array( 'menu' => $menu );
        if ( isset( $secondLevelLocationId ) && isset( $menu[$secondLevelLocationId] ) )
        {
            $parameters['submenu'] = $menu[$secondLevelLocationId];
        }
        if ($currentLocationId != NULL)
        {
            $parameters['currentLocationId'] = $currentLocationId;
        }
        return $this->render( 'xrowbootstrapBundle::page_topmenu.html.twig', $parameters, $response );
    }

    /**
     * @param string $identifier
     * @return \Knp\Menu\MenuItem
     */
    private function getMenu( $identifier )
    {
        return $this->container->get( "xrow.menu.$identifier" );
    }

    /**
     * @return \eZ\Publish\API\Repository\LocationService
     */
    private function getLocationService()
    {
        return $this->container->get( 'ezpublish.api.service.location' );
    }
    
    public function breadcrumbAction( $locationId )
    {
        /** @var WhiteOctober\BreadcrumbsBundle\Templating\Helper\BreadcrumbsHelper $breadcrumbs */
        $breadcrumbs = $this->get( "white_october_breadcrumbs" );
    
        $locationService = $this->getLocationService();
        $path = $locationService->loadLocation( $locationId )->path;
    
        // The root location can be defined at site access level
        $rootLocationId = $this->get( 'ezpublish.config.resolver' )->getParameter( 'content.tree_root.location_id' );
    
        /** @var eZ\Publish\Core\Helper\TranslationHelper $translationHelper */
        $translationHelper = $this->get( 'ezpublish.translation_helper' );
    
        $isRootLocation = false;
    
        // Shift of location "1" from path as it is not a fully valid location and not readable by most users
        array_shift( $path );
    
        for ( $i = 0; $i < count( $path ); $i++ )
        {
            $location = $locationService->loadLocation( $path[$i] );
            // if root location hasn't been found yet
            if ( !$isRootLocation )
            {
                // If we reach the root location We begin to add item to the breadcrumb from it
                if ( $location->id == $rootLocationId )
                {
                    $isRootLocation = true;
                    $breadcrumbs->addItem(
                        $translationHelper->getTranslatedContentNameByContentInfo( $location->contentInfo ),
                        $this->generateUrl( $location )
                    );
                }
            }
            // The root location has already been reached, so we can add items to the breadcrumb
            else
            {
                $breadcrumbs->addItem(
                    $translationHelper->getTranslatedContentNameByContentInfo( $location->contentInfo ),
                    $this->generateUrl( $location )
                );
            }
        }
    
        // We don't want the breadcrumb to be displayed if we are on the frontpage
        // which means we display it only if we have several items in it
        if ( count( $breadcrumbs ) <= 1 )
        {
            return new Response();
        }
        return $this->render(
            'xrowbootstrapBundle::breadcrumb.html.twig'
        );
    }
}
