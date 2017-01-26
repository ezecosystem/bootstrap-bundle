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

class LocationServiceCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('xrow:location')
            ->setDescription('Manage Subtree Location')
            ->addArgument('operation', InputArgument::REQUIRED, 'Operation to execute, either copy or move')
            ->addArgument('srcLocationId', InputArgument::REQUIRED, 'A subtree\'s root Location')
            ->addArgument('destinationParentLocationId', InputArgument::OPTIONAL, 'Parent location ID to copy/move to')
            ;

    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch command line arguments
        $operation = $input->getArgument( 'operation' );
        $srcLocationId = $input->getArgument( 'srcLocationId' );
        $destinationParentLocationId = $input->getArgument( 'destinationParentLocationId' );


        if ( $operation == 'copy' )
        {
            $copyData = $this->formatInputData($srcLocationId, $destinationParentLocationId, $operation);
            $this->getContentTypeContainer()->copy( $copyData );
        }
        else if ( $operation == 'move' )
        {
            $moveData = $this->formatInputData($srcLocationId, $destinationParentLocationId, $operation);
            $this->getContentTypeContainer()->move( $moveData );
        }
        else if ( $operation == 'delete' )
        {
            $deleteData = $this->formatInputData( $srcLocationId, null, $operation);
            $this->getContentTypeContainer()->delete( $deleteData );
        }
        else
        {
            $output->writeln( "<error>operation must be either copy or move</error>" );
            return;
        }

    }

    /**
     * Returns expected correct/formatted input data
     *
     * @param  string $srcLocationId                   Source LocationID
     * @param  string $destinationParentLocationId     Destination LocationID
     *
     * @return array  $expectedInputData               Correct formatted input data
     */
    public function formatInputData($srcLocationId, $destinationParentLocationId, $flag)
    {
        if( $flag == "move" )
            return $correctCopyData = array(
                "node" => $srcLocationId,
                "to" => $destinationParentLocationId
            );
        if( $flag == "copy")
            return $correctCopyData = array(
                "node" => $srcLocationId,
                "to" => $destinationParentLocationId
            );
        if ( $flag == "delete")
            return $correctCopyData = array(
                'node' =>  $srcLocationId
            );
    }

    /**
     * @return ContentTypeMigrationContainer
     */
    protected function getContentTypeContainer()
    {
        return $this->getContainer()->get('xrow.content_location_migration');
    }
}