<?php

namespace xrow\bootstrapBundle\Twig;

class TwigFiltersExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('wash_all', array($this, 'washAllFilter')),
            new \Twig_SimpleFilter('wash', array($this, 'washFilter')),
        );
    }

    public function washAllFilter($input)
    {
        return htmlentities($input);
    }

    public function washFilter($input)
    {
        return htmlspecialchars($input);
    }

    public function getName()
    {
        return 'xrow.bootstrap';
    }
}