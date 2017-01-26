<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Driver\PDOStatement;
use eZContentObjectTreeNode;
use eZContentCacheManager;

class viewCacheCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
        ->setDefinition(array(
                new InputOption('node-id', '', InputOption::VALUE_REQUIRED, 'node id for view cache'),
        ))
        ->setDescription('Clears the view cache for a specific node id')
        ->setHelp(<<<EOT
<info>Clearing view cache for node id</info>
ezpublish/console xrowcache:view:clear --node-id=2

EOT
        )
        ->setName('xrow:view:clear');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $legacyKernel = $this->getContainer()->get('ezpublish_legacy.kernel');
        $nodeID = $input->getOption('node-id');

        if($nodeID !== null)
        {
            $output->writeln('<comment>Clearing view cache for node id ' . $nodeID . '</comment>');

            $objectID = $legacyKernel()->runCallback(
                function () use ( $nodeID )
                {
                    $node = eZContentObjectTreeNode::fetch($nodeID);
                    $objectID = $node->ContentObject->ID;
                    eZContentCacheManager::clearContentCacheIfNeeded($objectID);
                    return $objectID;
                }
            );
            $output->writeln('<info>Cleared view cache for node id ' . $nodeID . ' and object id ' . $objectID . '</info>');
        }
        else
        {
            $output->writeln('<error>Please specify a node id with --node-id, see --help for more info</error>');
        }
    }
}