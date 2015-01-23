<?php
namespace xrow\bootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;


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

        $value = $yaml->parse(file_get_contents('/var/www/sites/plain/xrow/src/xrow/bootstrapBundle/Resources/config/style.yml'));
        $parameters = array_keys($value);
        $scss = "";
        foreach($parameters as $x)
        {
            $scss .= "\$custom-" . $x . ": " . $value[$x] . ";\n";
        }
        $file = fopen("/var/www/sites/plain/xrow/web/sass/customVariables.scss", "w") or die("Unable to open file!");
        fwrite($file, $scss);
        fclose($file);

    }
}