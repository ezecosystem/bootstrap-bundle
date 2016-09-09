<?php

namespace xrow\bootstrapBundle\Model\Migrations;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Doctrine\DBAL\Migrations\AbstractMigration,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    eZ\Publish\Core\Base\Exceptions\NotFoundException;

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
        // add attribute
        $this->getContentTypeContainer()->move( $moveData );
    }

    /**
     * Copy Location from A to B.
     *
     * @param copyData|null $copyData
     */
    protected function copy( $copyData = null ) {
        // add attribute
        $this->getContentTypeContainer()->copy( $copyData );
    }

    /**
     * Delete Location with ID A.
     *
     * @param deleteData|null $deleteData
     */
    protected function delete( $deleteData = null ) {
        // add attribute
        $this->getContentTypeContainer()->delete( $deleteData );
    }

    /**
     * @return ContentTypeMigrationContainer
     */
    protected function getContentTypeContainer()
    {
        return $this->getContainer()->get('xrow.content_location_migration');
    }
}