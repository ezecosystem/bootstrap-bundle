<?php

namespace xrow\bootstrapBundle\Model\Migrations;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Doctrine\DBAL\Migrations\AbstractMigration,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    Doctrine\DBAL\Schema\Schema;

abstract class abstractLocationMigration extends AbstractMigration implements ContainerAwareInterface {

    /**
     * Data with Location to COPY
     *
     * @var copyData
     */
    private $copyData;

    /**
     * Data with Location to MOVE
     *
     * @var moveData
     */
    private $moveData;

    /**
     * Data with Location to DELETE
     *
     * @var deleteData
     */
    private $deleteData;


    /**
     * ContainerInterface
     *
     * @var Container
     */
    private $container;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer( ContainerInterface $container = null )
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Move Location from A to B.
     *
     * @param moveData|null $moveData
     */
    protected function move( $moveData = null ) {
        if ( is_array( $moveData ) ) :
            foreach( $moveData as $key => $value) :
                // Delete Location
                $this->getContentTypeContainer()->move( $moveData[$key] );
            endforeach;
        else:
            echo "Data is not an Array";
        endif;
    }

    /**
     * Copy Location from A to B.
     *
     * @param copyData|null $copyData
     */
    protected function copy( $copyData = null ) {
        if ( is_array( $copyData ) ) :
            foreach( $copyData as $key => $value) :
                // Delete Location
                $this->getContentTypeContainer()->copy( $copyData[$key] );
            endforeach;
        else:
            echo "Data is not an Array";
        endif;
    }

    /**
     * Delete Location with ID A.
     *
     * @param deleteData|null $deleteData
     */
    protected function delete( $deleteData = null ) {

        if ( is_array( $deleteData ) ) :
            foreach( $deleteData as $key => $value) :
                // Delete Location
                $this->getContentTypeContainer()->delete( $deleteData[$key] );
            endforeach;
        else:
            echo "Data is not an Array";
        endif;
    }

    /**
     * @return ContentTypeMigrationContainer
     */
    protected function getContentTypeContainer()
    {
        return $this->getContainer()->get('xrow.content_location_migration');
    }

    /**
     * @param Schema $schema
     */
    public function up( Schema $schema )
    {
        // Get task
        $taskData = $this->task;

        if( is_array($taskData) ) {
            if( isset($taskData["copy"]) )
            // Copy/Move Location from A to B
            $this->copy( $taskData["copy"] );

            if( isset($taskData["move"]) )
            // Copy/Move Location from A to B
            $this->move( $taskData["move"] );

            if( isset($taskData["remove"]) )
            // Delete A
            $this->delete( $taskData["delete"] );
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }

}