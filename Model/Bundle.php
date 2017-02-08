<?php
namespace xrow\bootstrapBundle\Model;

use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Model\Bundle as GeneratorBundle;
/**
 * Represents a bundle being built.
 */
class Bundle extends GeneratorBundle
{
    private $namespace;
    
    private $name;
    
    private $targetDirectory;
    
    private $configurationFormat;
    
    private $isShared;
    
    private $testsDirectory;
    public function __construct($namespace, $name, $targetDirectory, $configurationFormat, $isShared)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->targetDirectory = $targetDirectory;
        $this->configurationFormat = $configurationFormat;
        $this->isShared = $isShared;
        $this->testsDirectory = $this->getTargetDirectory().'/Tests';
        parent::__construct($namespace, $name, $targetDirectory, $configurationFormat, $isShared);
    }
    /**
     * Returns the directory where the bundle will be generated.
     *
     * @return string
     */
    public function getTargetDirectory()
    {
        return rtrim($this->targetDirectory, '/').'/'.trim( strtolower(strtr(str_replace("Bundle", "-bundle", $this->namespace ), '\\', '/') ), '/');
    }
}