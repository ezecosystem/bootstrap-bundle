<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class BundleGeneratorCommand extends ContainerAwareCommand 
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('xrow:generate:bundle')
            ->setDescription('Create a new bundle')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of bundle')
            ->setHelp( "app/console xrow:generate:bundle --name=MostViewed");
            
    }
    //@TODO Bundle generator requires symfony generator 3.x, but conflicts https://github.com/ezsystems/ezstudio/blob/master/composer.json#L30
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $namespace = "Xrow/" . $name;
        $bundleName = $name . "Bundle";
        $projectRootDirectory = $this->getContainer()->getParameter('kernel.root_dir').'/..';
        
        $dir = $projectRootDirectory."/vendor/xrow/" . strtolower($name) . "-bundle";
        $process = new Process("app/console generate:bundle --namespace=" . $namespace . " --bundle-name=" . $name . "Bundle --no-interaction --format=annotation --dir=./vendor");
        $process->run();
        return; // Better code below...

        $bundle = new \Sensio\Bundle\GeneratorBundle\Model\Bundle(
            $namespace,
            $bundleName,
            $dir,
            'annotation',
            true
            );

        /** @var BundleGenerator $generator */
        $generator = $this->getGenerator();
 
        $generator->generateBundle($bundle);
        
        $package = array();
        $package["name"] = "xrow/".strtolower($name)."-bundle";
        $package["type"] = "symfony-bundle";
        $package["homepage"] = "symfony-bundle";
        $package["license"] = "Apache License 2.0";
        $package["authors"]["name"] = "xrow GmbH";
        $package["authors"]["homepage"] = "http://www.xrow.com";
        $package["require"]["php"] = "~5.5|~7.0";
        $package["autoload"]["psr-4"]["Xrow\\".$name."\\"] = "http://www.xrow.com";
        file_put_contents($file, $dir. "/composer.json", json_encode($package, JSON_PRETTY_PRINT));
        
        $process = new Process("git init" , $dir );
        $process->run();
        $process = new Process("git remote add origin git@gitlab.com:xrow-shared/". strtolower($name)."-bundle.git" , $dir );
        $process->run();
        $process = new Process("add ." , $dir );
        $process->run();
        $process = new Process("git commit" , $dir );
        $process->run();
        $process = new Process("git push -u origin master" , $dir );
        $process->run();
        
        $output->writeln($name . ' created!');
    }
}