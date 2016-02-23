<?php

namespace xrow\bootstrapBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */

class CheckXSS extends Constraint
{
    public $message = 'Ihre Eingabe enthält ungültige Zeichen';
    
    public function validatedBy()
    {
        return "xrow\bootstrapBundle\Validator\Constraints\CheckXSSValidator";
    }
}