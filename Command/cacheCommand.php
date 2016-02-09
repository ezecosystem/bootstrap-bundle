<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Driver\PDOStatement;

class cacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('xrowcache:ezdfsfile:clear')
            ->setDescription('Truncate (clear) the ezdfsfile cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getApplication()->getKernel()->getContainer();
        $connection = $this->container->get('doctrine.dbal.cluster_connection');
        $dbPlatform = $connection->getDatabasePlatform();
        $query = $dbPlatform->getTruncateTableSql('ezdfsfile_cache');
        $connection->executeUpdate($query);
    }
}