<?php

namespace App\Trait;

trait EnumFromNameTrait
{
    /**
     * Permet d'obtenir l'énumération à partir de son name.
     *
     * @param string $name
     * @return self
     * @throws \ValueError si aucun name ne correspond
     */
    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new \ValueError("\"$name\" is not a valid enum name for " . self::class);
    }
}
