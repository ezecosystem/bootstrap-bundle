<?php

namespace xrow\bootstrapBundle\Model\Migrations;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Doctrine\DBAL\Migrations\AbstractMigration,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    Doctrine\DBAL\Schema\Schema;

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
     * @param Array $addData    Array of class attributes to ADD
     */
    protected function add( $addData = null ) {

        if ( is_array( $addData ) ) :
            foreach( $addData as $key => $value) :
                // add attribute
                $this->getContentTypeContainer()->add( $addData[$key] );
            endforeach;
        else:
            echo "Data is not an Array";
        endif;
    }

    /**
     * Removes the class attribute.
     *
     * @param Array $addData    Array of class attributes to REMOVE
     */
    protected function remove( $removeData = null ) {

        if ( is_array( $removeData ) ) :
            foreach( $removeData as $key => $value) :
                // add attribute
                $this->getContentTypeContainer()->remove( $removeData[$key] );
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
        return $this->getContainer()->get('xrow.content_type_migration');
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Data with attributes to remove
        $removeData = $this->remove;

        // Remove Attribute
        $this->remove( $removeData );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // Data with attributes to add
        // $addData = $this->add;
        // Add Attribute
        // $this->add( $addData );
    }
}