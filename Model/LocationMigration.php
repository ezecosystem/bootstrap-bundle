<?php

namespace xrow\bootstrapBundle\Model;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Doctrine\DBAL\Migrations\AbstractMigration,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    xrow\bootstrapBundle\Model\ContentTypeMigration;

class LocationMigration extends ContentTypeMigration implements ContainerAwareInterface {

    /**
     * Data to COPY
     *
     * @var copyData
     */
    private $copyData;

    /**
     * Data to MOVE
     *
     * @var moveData
     */
    private $moveData;

    /**
     * Data to MOVE
     *
     * @var deleteData
     */
    private $deleteData;

    /**
     * Authorized user to perform the operation
     *
     * @var $loadUser
     */
    private $loadUser = 357217; //xrow = 357217

    /**
     * ContainerInterface
     *
     * @var Container
     */
    private $container;


    public function setContainer( ContainerInterface $container = null )
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function copy( $copyData = null ) {
        // Set loadUser
        $this->setLoadUser( $this->loadUser );
        // get Location data
        $this->setData( $copyData, "cp" );
        // copy Location
        $this->copyLocation();
    }

    public function move( $moveData = null ) {
        // Set loadUser
        $this->setLoadUser( $this->loadUser );
        // get Location data
        $this->setData( $moveData, "mv" );
        // move Location
        $this->moveLocation();
    }

    public function delete( $moveData = null ) {
        // Set loadUser
        $this->setLoadUser( $this->loadUser );
        // get Location data
        $this->setData( $moveData, "rm" );
        // delete Location
        $this->deleteLocation();
    }

    public function setData( $addData, $flag ) {
        if( $flag == "cp" )
            $this->copyData  = $addData;
        if( $flag == "mv" )
            $this->moveData  = $addData;
        if( $flag == "rm" )
            $this->deleteData  = $addData;
    }

    public function getCopyData() {
        return $this->copyData;
    }

    public function getMoveData() {
        return $this->moveData;
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     */
    public function copyLocation()
    {
        // Get ContentTypeService
        $contentTypeService = $this->getContentTypeService();

        // Get data with location to copy from/to
        $copy = $this->getCopyData();

        try
        {
            // Copy location
            $newLocation = $locationService->copySubtree( $copy['srcLocationId'], $copy['destinationParentLocationId'] );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            throw $e->getMessage();
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            throw $e->getMessage();
        }
         echo( "Location COPIED succesfully!" );
    }

    /**
     * Moves the subtree to $newParentLocation
     *
     */
    public function moveLocation()
    {
        // Get ContentTypeService
        $contentTypeService = $this->getContentTypeService();

        // Get data with location to move from/to
        $move = $this->getMoveData();

        try
        {
            // Move location
            $newLocation = $locationService->moveSubtree( $move['srcLocationId'], $move['newParentLocation'] );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            throw $e->getMessage();
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            throw $e->getMessage();
        }
         echo( "Location MOVED succesfully!" );
    }

    /**
     * Deletes $location and all its descendants
     *
     */
    public function deleteLocation()
    {
        // Get ContentTypeService
        $contentTypeService = $this->getContentTypeService();

        // Get data with location to delete
        $deleteID = $this->deleteData();

        try
        {
            // Delete location
            $deletedLocation = $locationService->deleteLocation( $deleteID );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            throw $e->getMessage();
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            throw $e->getMessage();
        }
         echo( "Location DELETED succesfully!" );
    }

    public function testCopyData()
    {
        $add = array(
            "srcLocationId" => 99999,
            "destinationParentLocationId" => 88888,
        );
        return $add;
    }

    public function testMoveData()
    {
        $remove = array(
            "srcLocationId" => 99999,
            "newParentLocation" => 88888,
        );
        return $remove;
    }

}