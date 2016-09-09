<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use stdClass;

class ContentTypeCommand extends ContainerAwareCommand
{
    /**
     * Repository eZ Publish
     *
     * @var Repository
     */
    protected $eZPublishRepository;

    /**
     * ContainerInterface
     *
     * @var Container
     */
    private $container;

    protected function configure()
    {
        $this
            ->setName('xrowmigration:class_attribute')
            ->setDescription('Migrate field definition/class attribute')
            ->addArgument('contenttype_identifier', InputArgument::REQUIRED, 'a content type/classname identifier')
            ->addArgument('field_identifier', InputArgument::REQUIRED, 'a field/class_attribute identifier')
            ->addOption(
                'add',
                'a',
                InputOption::VALUE_NONE,
                'add field definition/class_attribute'
            )
            ->addOption(
                'remove',
                'r',
                InputOption::VALUE_NONE,
                'remove field definition/class_attribute'
        );

    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch command line arguments
        $contentTypeIdentifier = $input->getArgument( 'contenttype_identifier' );
        $fieldDefinitionIdentifier = $input->getArgument( 'field_identifier' );

        $remove  = intval( $input->getOption('remove') );
        $add  = intval( $input->getOption('add') );

        // REMOVE
        if( isset($remove) && $remove == 1) {
            $this->getContentTypeContainer()->remove(
                $this->formatRemoveInput($contentTypeIdentifier, $fieldDefinitionIdentifier)
            );
        }

        // ADD
        $addTestData = $this->getContentTypeContainer()->testAddData();
        if( isset($add) && $add == 1) {
            $this->getContentTypeContainer()->add($addTestData);
        }

    }

    /**
     * Returns correct array format with class_identifier and class_attribute
     *
     * @param  string $contentTypeIdentifier     Content Class by Identifier. Ex.: file_audio
     * @param  string $fieldDefinitionIdentifier Contentclass Attribute Identifier. Ex.: xrowgis
     * @return array  $correctRemoveData         Correct formatted remove data
     *                                           Ex.: ["class_identifier"=> "file_audio", "class_attribute" => "xrowgis" ]
     */
    public function formatRemoveInput($contentTypeIdentifier, $fieldDefinitionIdentifier)
    {
        return $correctRemoveData = array(
            "class_identifier" => $contentTypeIdentifier,
            "class_attribute" => $fieldDefinitionIdentifier
        );

    }

    /**
     * @return ContentTypeMigrationContainer
     */
    protected function getContentTypeContainer()
    {
        return $this->getContainer()->get('xrow.content_type_migration');
    }
}