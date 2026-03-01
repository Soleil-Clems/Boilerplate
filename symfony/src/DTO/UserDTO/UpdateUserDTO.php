<?php

namespace App\DTO\UserDTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
class UpdateUserDTO
{
    #[Assert\Length(min: 5, max: 180, )]
    #[Assert\Email(message: 'The email must be a valid email address')]
    public ?string $email=null;

    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,;:#+=_\-])[A-Za-z\d@$!%*#?&.,;:+=_\-]{8,}$/",
        message: "The  new password must contain a minimum of 8 characters, including one uppercase letter, one lowercase letter, one number and one special character."
    )]
    public ?string $password=null;

    // ========== Uncomment if you want to add more fields to the update form ==========
    /*
    public ?string $firstname=null;

    public ?string $lastname=null;
    #[Assert\Regex(
        pattern: "/^\+?[1-9][0-9\s]{7,20}$/",
        message: "The phone number must contain between 8 and 20 digits, with an optional '+'."
    )]
    public ?string $phone=null;
    */

    /**
     * Conditional validation: if email is provided, password must also be provided to confirm the user's identity before allowing the email update.
     */
    #[Assert\Callback]
    public function validatePasswords(ExecutionContextInterface $context)
    {

        if ($this->email && !$this->password) {
            $context->buildViolation("password is required when you update email.")
                ->atPath("password")
                ->addViolation();
        }
    }

}
