<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Driver\PDOStatement;
use eZContentObjectTreeNode;
use eZContentObject;

class cacheCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
        ->setDefinition(array(
                new InputOption('cache', '', InputOption::VALUE_REQUIRED, 'The cache that will be cleared (view,ezdfsfile'),
                new InputOption('node-id', '', InputOption::VALUE_OPTIONAL, 'optional node id for view cache'),
        ))
        ->setDescription('Clears caches')
        ->setHelp(<<<EOT
<info>Clearing ezdfsfile cache</info>
ezpublish/console xrowcache:clear --cache=ezdfsfile

<info>Clearing view cache</info>
ezpublish/console xrowcache:clear --cache=view
                
<info>Clearing view for node id cache</info>
ezpublish/console xrowcache:clear --cache=view --node-id=2

EOT
        )
        ->setName('xrowcache:clear')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cache = $input->getOption('cache');

        if($cache == "view")
        {
            $repository = $this->getContainer()->get('ezpublish.api.repository');
            $legacyKernel = $this->getContainer()->get('ezpublish_legacy.kernel');
            $nodeID = $input->getOption('node-id');

            if($nodeID !== null)
            {
                $output->writeln('<info>Clearing view cache for node id ' . $nodeID . '</info>');

                $result = $legacyKernel()->runCallback(
                    function () use ( $nodeID )
                    {
                        return eZContentObjectTreeNode::fetch( $nodeID );
                    }
                );

                $objectID = $result->eZContentObject->ID;

                $result = $legacyKernel()->runCallback(
                    function () use ( $objectID )
                    {
                        $objectIDArray[] = $objectID;
                        return eZContentObject::clearCache( $objectIDArray );
                    }
                );
            }
            else
            {
                $output->writeln('<info>Clearing view cache</info>');

                $result = $legacyKernel()->runCallback(
                        function ()
                        {
                            return eZContentObject::clearCache();
                        }
                );
            }
        }
        elseif($cache == "ezdfsfile")
        {
            $output->writeln('<info>Clearing ezdfsfile cache</info>');

            $this->container = $this->getApplication()->getKernel()->getContainer();
            $connection = $this->container->get('doctrine.dbal.cluster_connection');
            $dbPlatform = $connection->getDatabasePlatform();
            $query = $dbPlatform->getTruncateTableSql('ezdfsfile_cache');
            $connection->executeUpdate($query);
        }
        else
        {
            $output->writeln('<error>Please specify a cache to clear, see --help for more info</error>');
        }
    }
}