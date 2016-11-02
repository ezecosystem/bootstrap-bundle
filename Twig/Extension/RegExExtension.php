<?php

namespace xrow\bootstrapBundle\Twig\Extension;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class RegExExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter( 'preg_filter', array($this, 'pregFilter') ),
            new Twig_SimpleFilter( 'preg_grep', array($this, 'pregGrep') ),
            new Twig_SimpleFilter( 'preg_match', array($this, 'pregMatch') ),
            new Twig_SimpleFilter( 'preg_quote', array($this, 'pregQuote') ),
            new Twig_SimpleFilter( 'preg_replace', array($this, 'pregReplace') ),
            new Twig_SimpleFilter( 'preg_split', array($this, 'pregSplit') ),
        );
    }

    public function pregFilter($subject, $pattern, $replacement='', $limit=-1)
    {
        if (!isset($subject)) {
            return null;
        }
        else {
            return preg_filter($pattern, $replacement, $subject, $limit);
        }
    }

    public function pregGrep($subject, $pattern)
    {
        if (!isset($subject)) {
            return null;
        }
        else {
            return preg_grep($pattern, $subject);
        }
    }

    public function pregMatch($subject, $pattern)
    {
        echo $pattern;
        if (!isset($subject)) {
            return null;
        }
        else {
            return preg_match($pattern, $subject);
        }
    }

    public function pregQuote($subject, $delimiter)
    {
        if (!isset($subject)) {
            return null;
        }
        else {
            return preg_quote($subject, $delimiter);
        }
    }

    public function pregReplace($subject, $pattern, $replacement='', $limit=-1)
    {
        if (!isset($subject)) {
            return null;
        }
        else {
            return preg_replace($pattern, $replacement, $subject);
        }
    }

    public function pregSplit($subject, $pattern)
    {
        if (!isset($subject)) {
            return null;
        }
        else {
            return preg_split($pattern, $subject);
        }
    }

    public function getName()
    {
        return 'xrow.twig.regex';
    }
}