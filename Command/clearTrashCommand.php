<?php

namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Input\ArrayInput;

use eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash\Handler;

#run the script like this: sudo php ezpublish/console clear:trashbin

class clearTrashCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clear:trashbin')
            ->setDescription('Deletes all objects of the trash bin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "Starting to clean up the trash bin.";
        $trashandler = $this->getContainer()->get('ezpublish.spi.persistence.legacy.trash.handler');
        $trashandler->emptyTrash();
        echo "Cleaning finished.";
    }
}