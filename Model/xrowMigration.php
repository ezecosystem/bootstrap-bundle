<?php
namespace xrow\bootstrapBundle\Model;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class xrowMigration extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function up(Schema $schema){}

    public function down(Schema $schema){}

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Deletes a fielddefinition from a contenttype.
     *
     * @param string  $contentTypeIdentifier
     * @param string  $fieldDefinitionIdentifier
     *
     * @throws -
     */
    public function removeFieldDefinition($contentTypeIdentifier, $fieldDefinitionIdentifier )
    {
        $repository = $this->container->get( 'ezpublish.api.repository' );

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 141333 ) );

        $contentTypeService = $repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);

        // Get the field definition that will be deleted
        $fieldDefinition = $contentType->fieldDefinitionsByIdentifier[$fieldDefinitionIdentifier];

        // Create a draft
        $contentTypeDraft = $contentTypeService->createContentTypeDraft($contentType);

        $contentTypeService->removeFieldDefinition($contentTypeDraft,$fieldDefinition);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

    }

    /**
     * Adds a fielddefinition to a contenttype.
     *
     * @param string                       $contentTypeIdentifier
     * @param FieldDefinitionCreateStruct  $fieldCreateStruct
     *
     * @throws -
     */
    public function addFieldDefinition($contentTypeIdentifier, $fieldCreateStruct)
    {
        $repository = $this->container->get( 'ezpublish.api.repository' );

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 141333 ) );
        
        $contentTypeService = $repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        
        // Create a draft
        $contentTypeDraft = $contentTypeService->createContentTypeDraft($contentType);

        // Add the field to the draft
        $contentTypeService->addFieldDefinition($contentTypeDraft, $fieldCreateStruct);
        
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

    }
}