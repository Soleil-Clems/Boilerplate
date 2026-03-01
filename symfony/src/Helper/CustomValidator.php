<?php

namespace App\Helper;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(object|array $values): array
    {
        $errorList = [];
        if (is_array($values)) {
            foreach ($values as $value) {

                $errors = $this->validator->validate($value);

                foreach ($errors as $error) {
                    $errorList[] = $error->getMessage();
                }
            }
        } else {
            $errors = $this->validator->validate($values);

            foreach ($errors as $error) {
                $errorList[] = $error->getMessage();
            }
        }

        return $errorList;
    }
}
