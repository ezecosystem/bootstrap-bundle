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
        $addData = $this->formatInputAddData( $addData );
        $this->addData  = $addData;
    }

    public function getAddData() {
        return $this->addData;
    }

    public function setRemoveData( $removeData ) {
        $convertedData = $this->formatInputRemoveData( $removeData );
        $this->removeData = $convertedData;
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

        // If Position is not defined make it last+1
        if( $add["contentclass_attribute"]["field_structure"]["position"] == "" )
            $add["contentclass_attribute"]["field_structure"]["position"] = (count($contentType->fieldDefinitionsById) + 1);

        // Save any possible content type draft
        $this->saveContentTypeDraft( $contentType->id );

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
            echo $add["contentclass_attribute"]["identifier"] . " Exception error: " . $e->getMessage() . "\n\r";
            return;
        }

         echo "FieldDefinition ".$add["contentclass_attribute"]["identifier"]." added succesfully!\n\r";
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

        // Save any possible content type draft
        $this->saveContentTypeDraft( $contentType->id );

        try
        {
            // Create a draft
            $contentTypeDraft = $contentTypeService->createContentTypeDraft( $contentType );

            if( isset($fieldDefinition)) {
                // fieldDefinition to REMOVE from contentTypeDraft
                $removeFields = $contentTypeService->removeFieldDefinition( $contentTypeDraft, $fieldDefinition );
            }
            // Save/Publish (contentTypeDraft)
            $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        }
        catch ( \Exception $e )
        {
            echo "ContentType: ".$removeData["class_attribute"] . " Exception error: " . $e->getMessage() . "\n\r";
            return;
        }

         echo "FieldDefinition ".$removeData["class_attribute"]." removed succesfully!\n\r";
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
        if( isset($contentType->fieldDefinitionsByIdentifier[ $fieldDefinitionIdentifier ]) )
        {
            $fieldDefinition = $contentType->fieldDefinitionsByIdentifier[ $fieldDefinitionIdentifier ];
            return $fieldDefinition;
        }
        else
        {
            echo( "FieldDefinition with identifier $fieldDefinitionIdentifier not found\n" );
            return;
        }
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

    /**
     * Save any created content class/type draft
     * @param  integer $id Content Class ID
     * @return string      Error message in case of failure
     */
    public function saveContentTypeDraft( $id )
    {
        // Get ContentTypeService
        $contentTypeService = $this->getContentTypeService();

        //Save any possible existing draft
        try
        {
            $contentTypeDraft = $contentTypeService->loadContentTypeDraft( $id );
            if( $contentTypeDraft )
                $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        }
        catch ( \Exception $e ){}
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

    /**
     * Format input data to valid format to delete class attribute
     *
     * @param  array $inputData     input REMOVE data
     * @return array                correct formatted REMOVE data
     */
    public function formatInputRemoveData( $inputData )
    {
        $class_identifier = current(array_keys( $inputData ));

        $convertedData["class_identifier"] = $class_identifier;
        $convertedData["class_attribute"] = $inputData[$class_identifier];

        return $convertedData;
    }

    /**
     * Format input data to valid format to add class attribute
     *
     * @param  array $inputData     input ADD data
     * @return array                correct formatted ADD data
     */
    public function formatInputAddData( $inputData )
    {
        // first key contains the class identifier. Ex.: file_audio, folder, frontpage etc
        $class_identifier = current(array_keys($inputData));
        try {
            $data = array(
            "contentclass_identifier" => $class_identifier, // Required
                    "contentclass_attribute" => array(
                        "identifier" => $inputData[$class_identifier]["identifier"], // Required Ex: xrowgis, headline
                        "data_type_string" => $inputData[$class_identifier]["type"],// Required Ex.: xrowgis, ezstring
                        "field_structure" => array( // is converted later to object
                                    "names" => $inputData[$class_identifier]["names"], // Required | array()
                                    "descriptions" => $inputData[$class_identifier]["descriptions"], // Required | array()
                                    "fieldGroup" => $inputData[$class_identifier]["fieldGroup"],  //
                                    "position" => $inputData[$class_identifier]["position"], // Required last|
                                    "isTranslatable" => $inputData[$class_identifier]["isTranslatable"], // Required
                                    "isRequired" => $inputData[$class_identifier]["isRequired"], // Required
                                    "isInfoCollector" => $inputData[$class_identifier]["isInfoCollector"], // Required
                                    // "defaultValue" => $inputData[$class_identifier]["options"], // array()
                        )
                    )
            );
        }
        catch ( \Exception $e )
        {
            echo "Wrong input data";
        }

        return $data;
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

    /**
     * Example of correct input data for ADDING attributes and testing
     *
     * @return Array $add
     */
    public function testAddData()
    {
        $add = array(
                    array( "file_audio" => array(
                                   "identifier" => "zztop", // Required Ex: xrowgis, headline
                                    "type" => "xrowgis",// Required Ex.: xrowgis, ezstring
                                    "names" => array( "ger-DE" => "ZZTop" ), // Required | array
                                    "descriptions" => array( "ger-DE" => "ZZ Top is a band " ), // Required | array
                                    "fieldGroup" => "",
                                    "position" => 11, // Required
                                    "isTranslatable" => false, // Required boolean | default false
                                    "isRequired" => false, // Required boolean | default false
                                    "isInfoCollector" => false, // Required boolean | default false
                                    "isSearchable" => true,  // Required boolean | default false
                                    // "options" => array()
                        )
                    ),
        );
        return $add;
    }

    /**
     * Example of correct input data for REMOVING attributes and testing
     *
     * @return Array $remove
     */
    public function testRemoveData()
    {
        $remove = array(
            array( "file_audio" => "zztop"), // remove from: class_identifier (key) => the following: class_attribute (value)
            // array( "file_audio" => "zztop"),
        );

        return $remove;
    }
}