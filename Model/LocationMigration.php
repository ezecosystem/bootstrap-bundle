<?php

namespace xrow\bootstrapBundle\Model;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
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
     * Data to DELETE
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

    public function copy( $data = null ) {
        // Preprocess data
        $this->preprocess( $data, "cp" );
        // copy Location
        $this->copyLocation();
    }

    public function move( $data = null ) {
        // Preprocess data
        $this->preprocess( $data, "mv" );
        // move Location
        $this->moveLocation();
    }

    public function delete( $data = null ) {
        // Preprocess data
        $this->preprocess( $data, "rm" );
        // delete Location
        $this->deleteLocation();
    }

    private function preprocess( $data, $type )
    {
        // Set loadUser
        $this->setLoadUser( $this->loadUser );
        // format Location data
        $data = $this->formatLocationInputData( $data, $type );
        // set Location data to COPY
        $this->setData( $data, $type );
    }

    /**
     * Sets data with location data to move|copy|delete
     * @param Array $locationData   data with location id and new destination
     * @param String $flag          Either: cp|mv|rm
     */
    public function setData( $locationData, $flag ) {
        if( $flag == "cp" )
            $this->copyData  = $locationData;
        if( $flag == "mv" )
            $this->moveData  = $locationData;
        if( $flag == "rm" )
            $this->deleteData  = $locationData;
    }

    /**
     * Gets data with location data to move|copy|delete
     * @param String $flag          Either: cp|mv|rm
     * @return correct data
     */
    public function getData( $flag ) {
        if( $flag == "cp" )
            return $this->copyData;
        if( $flag == "mv" )
            return $this->moveData;
        if( $flag == "rm" )
            return $this->deleteData;
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     */
    public function copyLocation()
    {
        // Get LocationService
        $locationService = $this->getLocationService();

        // Get data with location to copy from/to
        $copy = $this->getData( "cp" );

        // Load Location Instances
        $srcLocationId = $locationService->loadLocation( $copy['srcLocationId'] );
        $destinationParentLocationId = $locationService->loadLocation( $copy['destinationParentLocationId'] );

        try
        {
            // Copy location
            $newLocation = $locationService->copySubtree( $srcLocationId, $destinationParentLocationId );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            throw $e->getMessage();
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            throw $e->getMessage();
        }

         echo "Location: ".$copy['destinationParentLocationId']." COPIED to: ". $copy['srcLocationId']." was succesfull!\n\r";
    }

    /**
     * Moves the subtree to $newParentLocation
     *
     */
    public function moveLocation()
    {
        // Get LocationService
        $locationService = $this->getLocationService();

        // Get data with location to move from/to
        $move = $this->getData( "mv" );

        // Load Location Instances
        $srcLocationId = $locationService->loadLocation( $move['srcLocationId'] );
        $newParentLocationId = $locationService->loadLocation( $move['newParentLocation'] );

        try
        {
            // Move location
            $newLocation = $locationService->moveSubtree( $srcLocationId, $newParentLocationId );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            throw $e->getMessage();
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            throw $e->getMessage();
        }

         echo "Location: ".$move['srcLocationId']." MOVED to: ". $move['newParentLocation']." was succesfull!\n\r";
    }

    /**
     * Deletes $location and all its descendants
     *
     */
    public function deleteLocation()
    {
        // Get LocationService
        $locationService = $this->getLocationService();

        // Get data with location to delete
        $delete = $this->deleteData;

        // Load Location Instance
        $srcLocationId = $locationService->loadLocation( $delete['srcLocationId'] );

        try
        {
            // Delete location
            $deletedLocation = $locationService->deleteLocation( $srcLocationId );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            throw $e->getMessage();
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            throw $e->getMessage();
        }

         echo "Location ".$delete['srcLocationId']." DELETED succesfully!\n\r";
    }

    public function formatLocationInputData( $inputData = [], $flag = null )
    {
        $class_identifier = current(array_keys( $inputData ));

        $correctLocationData["srcLocationId"] = $inputData["node"] ? $inputData["node"] : $inputData[0];
        if( $flag == "cp" )
            $correctLocationData["destinationParentLocationId"] = $inputData["to"];
        if( $flag == "mv" )
            $correctLocationData["newParentLocation"] = $inputData["to"];

        return $correctLocationData;
    }

    public function exampleCopyData()
    {
        $copyData = array(
            "srcLocationId" => 725221,
            "destinationParentLocationId" => 365221,
        );
        return $copyData;
    }

    public function exampleMoveData()
    {
        $moveData = array(
            "srcLocationId" => 725221,
            "newParentLocation" => 365221,
        );
        return $remove;
    }
}