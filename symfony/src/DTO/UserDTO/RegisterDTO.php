<?php

namespace App\DTO\UserDTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterDTO
{
    #[Assert\Length(min: 5, max: 180, )]
    #[Assert\Email(message: 'The email must be a valid email address')]
    public string $email;

    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,;:#+=_\-])[A-Za-z\d@$!%*#?&.,;:+=_\-]{8,}$/",
        message: "The password must contain a minimum of 8 characters, including one uppercase letter, one lowercase letter, one number and one special character."
    )]
    public string $password;

    #[Assert\NotBlank(message: 'The confirm password cannot be blank')]
    public string $confirm_password;

    // ========== Uncomment if you want to add more fields to the registration form ==========
    /*
    #[Assert\NotBlank(message: 'The first name cannot be blank')]
    public string $firstname;
    #[Assert\NotBlank(message: 'The last name cannot be blank')]
    public string $lastname;

    /*
    #[Assert\Regex(
        pattern: "/^\+?[1-9][0-9\s]{7,20}$/",
        message: "The phone number must contain between 8 and 20 digits, with an optional '+'."
    )]
    public string $phone;
    */
}
