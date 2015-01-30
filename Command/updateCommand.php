<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Input\ArrayInput;



class updateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('style:update')
            ->setDescription('Updates the CSS Styles from YML');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yaml = new Parser();
        $output->writeln(dirname(__FILE__));
        $value = $yaml->parse(file_get_contents(dirname(__FILE__).'/../Resources/config/style.yml'));
        $parameters = array_keys($value);
        $scss = "";
        foreach($parameters as $x)
        {
            
            if((strpos($value[$x], ".jpg") || strpos($value[$x], ".png")) != false)
            {
                $scss .= "\$" . $x . ": url(\"" . $value[$x] . "\");\n";
            }
            else 
            {
                $scss .= "\$" . $x . ": " . $value[$x] . ";\n";
            }
        }
        $file = fopen(dirname(__FILE__).'/../../../../web/sass/customVariables.scss', "w") or die("Unable to open file!");
        fwrite($file, $scss);
        fclose($file);
        $command = $this->getApplication()->find('assetic:dump');
        $arguments = array(
            ''
        );
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);
        if($returnCode == 0) {
            $output->writeln('styles applied successfully ...');
        }
        
    }
}
