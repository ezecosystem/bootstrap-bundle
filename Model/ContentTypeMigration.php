<?php

namespace xrow\bootstrapBundle\Model;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Doctrine\DBAL\Migrations\AbstractMigration,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    eZ\Publish\Core\Base\Exceptions\NotFoundException;

class ContentTypeMigration implements ContainerAwareInterface {

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


    public function add( $addData = null ) {
        // Set loadUser
        $this->setLoadUser( $this->loadUser );
        // get attribute data
        $this->setAddData( $addData );
        // add attribute
        $this->addAttribute();
    }

    public function remove( $removeData = null ) {
        // Set loadUser
        $this->setLoadUser( $this->loadUser );
        // get attribute data
        $this->setRemoveData( $removeData );
        // add attribute
        $this->removeAttribute();
    }

    public function setAddData( $addData ) {
        $this->addData  = $addData;
    }

    public function getAddData() {
        return $this->addData;
    }

    public function setRemoveData( $removeData ) {
        $this->removeData = $removeData;
    }

    public function getRemoveData() {
        return $this->removeData;
    }

    public function setLoadUser( $loadUser ) {
        $this->loadUser = $loadUser;
    }

    public function getLoadUser() {
        return $this->loadUser;
    }

    public function setContainer( ContainerInterface $container = null )
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /* Add ezcontentclass_attribute to EXISTING ezcontentclass
     * Ex.: zztop, file_audio
     *
     */
    public function addAttribute()
    {
        // Get ContentTypeService
        $contentTypeService = $this->getContentTypeService();

        // Get attribute to add
        $add = $this->getAddData();

        // Create ContentType Structure to update
        $contentTypeCreateStruct = $contentTypeService->newContentTypeUpdateStruct( $add["contentclass_identifier"] ); //Ex: file_audio

        //Create new FieldStructure/Attribute. Ex.: 'title', 'ezstring'
        $addFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct(
                $add["contentclass_attribute"]["identifier"],
                $add["contentclass_attribute"]["data_type_string"]
            );

        // Add additional data to FieldStructure/Attribute
        $addFieldCreateStruct = $this->createObjectFromArray(
                $addFieldCreateStruct,
                $add["contentclass_attribute"]["field_structure"]
            );


        // Load EXISTING content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier(
            $add["contentclass_identifier"]
        );

        try
        {
            // Create a draft from Existing
            $contentTypeDraft = $contentTypeService->createContentTypeDraft( $contentType );

            // Add the field to the draft
            $contentTypeService->addFieldDefinition($contentTypeDraft, $addFieldCreateStruct);

            // Save/Publish (contentTypeDraft)
           $contentTypeService->publishContentTypeDraft($contentTypeDraft);
       }
        catch ( \Exception $e )
        {
            throw $e;
        }
         echo( "FieldDefinition added succesfully!" );
    }

    /* Remove ezcontentclass_attribute from EXISTING ezcontentclass
     * Ex.: ["class_identifier" => "xrowgis", "class_attribute" => "file_audio"]
     *
     * @param array                        $removeData
     */
    public function removeAttribute()
    {
        // Get ContentTypeService
        $contentTypeService = $this->getContentTypeService();

        // Get attribute to add
        $removeData = $this->getRemoveData();


        // Get ContentType
        $contentType = $this->getContentType( $contentTypeService, $removeData["class_identifier"]);

        //Get FieldDefinition from $contentType
        $fieldDefinition = $this->getExistingFieldDefinition( $contentType, $removeData["class_attribute"]);

        try
        {
            // Create a draft
            $contentTypeDraft = $contentTypeService->createContentTypeDraft( $contentType );
            // fieldDefinition to REMOVE from contentTypeDraft
            $removeFields = $contentTypeService->removeFieldDefinition($contentTypeDraft, $fieldDefinition);
            // Save/Publish (contentTypeDraft)
            $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        }
        catch ( \Exception $e )
        {
            throw $e;
        }
         echo( "FieldDefinition removed succesfully!" );
    }

    /* Get ContentTypeGroup from object (Ex: $obj->loadContentTypeGroupByIdentifier[Media])
     *
     * @param object                        $contentTypeService
     * @param string                        $groupIdentifier
     */
    public function getContentTypeGroup( $contentTypeService, $groupIdentifier )
    {
        try
        {
            $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier( $groupIdentifier );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            echo( "ContentTypeGroup with identifier $groupIdentifier not found" );
            return;
        }
        return $contentTypeGroup;
    }

    /* Get ContentType from object (Ex: $obj->loadContentTypeByIdentifier[file_audio])
     *
     * @param object                        $contentTypeService
     * @param string                        $contentTypeIdentifier
     */
    public function getContentType( $contentTypeService, $contentTypeIdentifier )
    {
        try
        {
            $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier); 
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
           echo( "ContentType with identifier $contentTypeIdentifier not found" );
            return;
        }
        return $contentType;
    }

    /* Get Field definition from ContentType object (Ex: $obj->fieldDefinitionsByIdentifier[xrowgis])
     *
     * @param object                        $contentType
     * @param string                        $fieldDefinitionIdentifier
     */
    public function getExistingFieldDefinition( $contentType, $fieldDefinitionIdentifier )
    {
        try
        {
            $fieldDefinition = $contentType->fieldDefinitionsByIdentifier[ $fieldDefinitionIdentifier ];
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            echo( "FieldDefinition with identifier $fieldDefinition in contentType $contentType not found" );
            return;
        }
        return $fieldDefinition;
    }

    /* Get repository
     *
     */
    public function getRepository()
    {

        $loadUserID = $this->getLoadUser();
        $repository = $this->getContainer()->get( "ezpublish.api.repository" );
        $repository->setCurrentUser( $repository->getUserService()->loadUser( $loadUserID ) );

        return $repository;
    }

    /* Get ContentTypeService
     *
     * @param object                        $repository
     */
    public function getContentTypeService ( $repository = null )
    {
        $repository = $repository === null ? $this->getRepository() : $repository;
        $contentTypeService = $repository->getContentTypeService();

        return $contentTypeService;
    }

    /* Get LocationService
     *
     * @param object                        $repository
     */
    public function getLocationService ( $repository = null )
    {
        $repository = $repository === null ? $this->getRepository() : $repository;
        $locationService = $repository->getLocationService();

        return $locationService;
    }

    /* Build object from given array
     *
     * @param object                        $obj
     * @param array                         $(add)Array
     */
    public function createObjectFromArray( $obj, $addArray )
    {
        foreach( $addArray as $key => $value ) {
            $obj->$key = $value;
        }
        return $obj;
    }

    public function testAddData()
    {
        $add = array(
                "Name" => "Audio",
                "contentclass_identifier" => "file_audio", //$contentTypeIdentifier = file_audio
                "names" => array("ger-DE" => "Audio"), // Required
                "main_language_code" => "ger-DE", // Required
                "contentclass_attribute" => array(
                    "identifier" => "zztop", //Ex: xrowgis, headline
                    "data_type_string" => "xrowgis",// Ex.: xrowgis, ezstring
                    "field_structure" => array(
                                "names" => array( "ger-DE" => "ZZTop" ),
                                "descriptions" => array( "ger-DE" => "ZZ Top is a band " ),
                                "fieldGroup" => "",
                                "position" => 11,
                                "isTranslatable" => false,
                                "isRequired" => false,
                                "isInfoCollector" => false,
                                "defaultValue" => array(
                                        "latitude" => 0, // requires int
                                        "longitude" => 0, // requires int
                                        "street" => "",
                                        "zip" => "",
                                        "district" => "",
                                        "city" => "",
                                        "state" => "",
                                        "country" => "",
                                ),
                                "isSearchable" => true,
                        )
                    )
                );
        return $add;
    }

    public function testRemoveData()
    {
        $remove = array(
            "class_identifier" => "file_audio",
            "class_attribute"=> "zztop"
        );
        return $remove;
    }

}