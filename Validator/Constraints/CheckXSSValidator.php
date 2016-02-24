<?php

namespace xrow\bootstrapBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */

class CheckXSSValidator extends ConstraintValidator
{
    public function __construct()
    {
    }
    
    public function validate($input, Constraint $constraint)
    {
        if ( preg_match("/.*(<|>|:|\(\)).*/", $input) ) {
            $this->context->addViolation($constraint->message);
        }
    }
}