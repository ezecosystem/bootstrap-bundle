<?php

namespace xrow\bootstrapBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */

class CheckXSS extends Constraint
{
    public $message;
    
    public function validatedBy()
    {
        return "xrow\bootstrapBundle\Validator\Constraints\CheckXSSValidator";
    }
}