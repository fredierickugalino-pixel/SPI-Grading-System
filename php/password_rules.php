<?php
function passwordMeetsRequirements(string $password): bool
{
    return strlen($password) >= 8
        && preg_match("/[a-z]/", $password) === 1
        && preg_match("/[A-Z]/", $password) === 1
        && preg_match("/[^a-zA-Z0-9]/", $password) === 1;
}
