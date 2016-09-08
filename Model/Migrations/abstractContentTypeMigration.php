<?php

namespace xrow\bootstrapBundle\Model\Migrations;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Doctrine\DBAL\Migrations\AbstractMigration,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    eZ\Publish\Core\Base\Exceptions\NotFoundException;

abstract class abstractContentTypeMigration extends AbstractMigration implements ContainerAwareInterface {

    /**
     * Data to add
     *
     * @var addData
     */
    private $addData;

    /**
     * Data to remove
     *
     * @var removeData
     */
    private $removeData;



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
     * Adds the class attribute.
     *
     * @param addData|null $addData
     */
    public function add( $addData = null ) {
        // add attribute
        $this->getContentTypeContainer()->add($addData);
    }

    /**
     * Removes the class attribute.
     *
     * @param removeData|null $removeData
     */
    public function remove( $removeData = null ) {
        // add attribute
        $this->getContentTypeContainer()->remove( $removeData );
    }

    /**
     * @return ContentTypeMigrationContainer
     */
    protected function getContentTypeContainer()
    {
        return $this->getContainer()->get('xrow.content_type_migration');
    }
}