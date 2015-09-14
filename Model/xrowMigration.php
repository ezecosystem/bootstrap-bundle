<?php
namespace xrow\bootstrapBundle\Model;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

abstract class xrowAbstractMigration extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * Adds an SH script from file to execute during migration
     *
     * @param string  $path
     * @param string  $parameters
     *
     * @throws AbortMigrationException
     */
    public function executeScript($path, $parameters = "")
    {
        if(preg_match("/^.*\.(sh)$/i", $path) === 1)
        {
            $process = new Process("sh " . $path . $parameters);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }
        }
        else
        {
            throw new AbortMigrationException("File " . $path . " must be a SH file!");
        }
    }

    /**
     * Adds SQL statements from file to execute during migration
     *
     * @param string  $path
     * 
     * @throws AbortMigrationException
     */
    public function addSQLFile($path)
    {
        if(preg_match("/^.*\.(sql)$/i", $path) === 1)
        {
            $sql = file_get_contents($path);
            $this->version->addSql($sql);
        }
        else
        {
            throw new AbortMigrationException("File " . $path . " must be a SQL file!");
        }
    }

    /**
     * Deletes a fielddefinition from a contenttype.
     *
     * @param string  $contentTypeIdentifier
     * @param string  $fieldDefinitionIdentifier
     */
    public function removeFieldDefinition($contentTypeIdentifier, $fieldDefinitionIdentifier)
    {
        $repository = $this->container->get("ezpublish.api.repository");
        $repository->setCurrentUser($repository->getUserService()->loadUser(141333));

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
     */
    public function addFieldDefinition($contentTypeIdentifier, $fieldCreateStruct)
    {
        $repository = $this->container->get("ezpublish.api.repository");
        $repository->setCurrentUser($repository->getUserService()->loadUser(141333));

        $contentTypeService = $repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);

        // Create a draft
        $contentTypeDraft = $contentTypeService->createContentTypeDraft($contentType);

        // Add the field to the draft
        $contentTypeService->addFieldDefinition($contentTypeDraft, $fieldCreateStruct);

        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
    }
}