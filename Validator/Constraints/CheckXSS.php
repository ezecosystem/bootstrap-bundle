<?php

namespace xrow\bootstrapBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */

class CheckXSS extends Constraint
{
    
    /**
     * @Assert\NotBlank(message = "xrowbootstrap.checkxss.invalidcharacters")
     */
    public $message;
    
    public function validatedBy()
    {
        return "xrow\bootstrapBundle\Validator\Constraints\CheckXSSValidator";
    }
}