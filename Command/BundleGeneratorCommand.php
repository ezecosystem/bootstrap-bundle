<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Composer\Json\JsonFile;
use Symfony\Component\Filesystem\Filesystem;
use xrow\bootstrapBundle\Model\Bundle;

class BundleGeneratorCommand extends GeneratorCommand 
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
            ->setHelp( "app/console xrow:generate:bundle MostViewed");
            
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Have you created git@gitlab.com:xrow-shared/". strtolower($name) . "-bundle.git? Continue with this action?", false, '/^(y|j)/i');

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $namespace = "Xrow/" . $name;
        $bundleName = $name . "Bundle";
        $projectRootDirectory = $this->getContainer()->getParameter('kernel.root_dir').'/..';
        
        $dir = $projectRootDirectory."/src";

        $bundle = new Bundle(
            $namespace,
            $bundleName,
            $dir,
            'annotation',
            true
            );

        /** @var BundleGenerator $generator */
        $generator = $this->getGenerator();
 
        $generator->generateBundle($bundle);
        $bundlebasename = strtolower($name)."-bundle";
        $package = array();
        $package["name"] = "xrow/". $bundlebasename;
        $package["type"] = "symfony-bundle";
        $package["homepage"] = "symfony-bundle";
        $package["license"] = "Apache License 2.0";
        $package["authors"]["name"] = "xrow GmbH";
        $package["authors"]["homepage"] = "http://www.xrow.com";
        $package["require"]["php"] = "~5.5|~7.0";
        $package["autoload"]["psr-4"][$bundle->getNamespace() . "\\"] = "";
        file_put_contents( $bundle->getTargetDirectory() . "/composer.json", json_encode($package, JSON_PRETTY_PRINT));
        $repo = "git@gitlab.com:xrow-shared/". $bundlebasename . ".git";

        
        $json = new JsonFile( $projectRootDirectory . "/composer.json");
        $composer = $json->read();
        $composer["repositories"][] = array(
            "type" => "vcs",
            "url" => $repo
        );
        $composer["require"]["xrow/" . $bundlebasename] = "dev-master";
        $json->write($composer);
        
        
        $process = new Process("git init" , $bundle->getTargetDirectory() );
        $process->run();
        $process = new Process("git remote add origin " . $repo , $bundle->getTargetDirectory() );
        $process->run();
        $process = new Process("git add ." , $bundle->getTargetDirectory() );
        $process->run();
        $process = new Process("git commit -a -m\"Initial Create\"" , $bundle->getTargetDirectory() );
        $process->run();
        $process = new Process("git push -u origin master" , $bundle->getTargetDirectory() );
        $process->run();
        $fs = new Filesystem();
        $fs->remove($bundle->getTargetDirectory());

        #$output->writeln( 'Composer run' );
        #$process = new Process("composer update" , $projectRootDirectory );
        #$process->run();
        $output->writeln( $name . ' created');
    }
    protected function updateKernel(OutputInterface $output, KernelInterface $kernel, Bundle $bundle)
    {
        $kernelManipulator = new KernelManipulator($kernel);
    
        $output->writeln(sprintf(
            '> Enabling the bundle inside <info>%s</info>',
            $this->makePathRelative($kernelManipulator->getFilename())
            ));
    
        try {
            $ret = $kernelManipulator->addBundle($bundle->getBundleClassName());
    
            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);
    
                return array(
                    sprintf('- Edit <comment>%s</comment>', $reflected->getFilename()),
                    '  and add the following bundle in the <comment>AppKernel::registerBundles()</comment> method:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $bundle->getBundleClassName()),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>AppKernel::registerBundles()</comment>.', $bundle->getBundleClassName()),
                '',
            );
        }
    }
    // Copied functoins from Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;
    protected function updateRouting(OutputInterface $output, Bundle $bundle)
    {
        $targetRoutingPath = $this->getContainer()->getParameter('kernel.root_dir').'/config/routing.yml';
        $output->writeln(sprintf(
            '> Importing the bundle\'s routes from the <info>%s</info> file',
            $this->makePathRelative($targetRoutingPath)
            ));
        $routing = new RoutingManipulator($targetRoutingPath);
        try {
            $ret = $routing->addResource($bundle->getName(), $bundle->getConfigurationFormat());
            if (!$ret) {
                if ('annotation' === $bundle->getConfigurationFormat()) {
                    $help = sprintf("        <comment>resource: \"@%s/Controller/\"</comment>\n        <comment>type:     annotation</comment>\n", $bundle->getName());
                } else {
                    $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing.%s\"</comment>\n", $bundle->getName(), $bundle->getConfigurationFormat());
                }
                $help .= "        <comment>prefix:   /</comment>\n";
    
                return array(
                    '- Import the bundle\'s routing resource in the app\'s main routing file:',
                    '',
                    sprintf('    <comment>%s:</comment>', $bundle->getName()),
                    $help,
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already imported.', $bundle->getName()),
                '',
            );
        }
    }
    
    protected function updateConfiguration(OutputInterface $output, Bundle $bundle)
    {
        $targetConfigurationPath = $this->getContainer()->getParameter('kernel.root_dir').'/config/config.yml';
        $output->writeln(sprintf(
            '> Importing the bundle\'s %s from the <info>%s</info> file',
            $bundle->getServicesConfigurationFilename(),
            $this->makePathRelative($targetConfigurationPath)
            ));
        $manipulator = new ConfigurationManipulator($targetConfigurationPath);
        try {
            $manipulator->addResource($bundle);
        } catch (\RuntimeException $e) {
            return array(
                sprintf('- Import the bundle\'s "%s" resource in the app\'s main configuration file:', $bundle->getServicesConfigurationFilename()),
                '',
                $manipulator->getImportCode($bundle),
                '',
            );
        }
    }
    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }
}