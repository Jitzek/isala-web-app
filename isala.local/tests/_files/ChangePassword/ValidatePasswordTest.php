<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * UnitTest
 * @group Unit
 */

// ClassName and FileName needs to end with "Test" (capitalizing shouldn't matter)
class validatePasswordTest extends TestCase
{
    // FunctionName needs to start with "test" (capitalizing shouldn't matter)
    /** @test */
    public function validatePassword_succesful1()
    {
        $prev_password = 'password';
        $new_password = 'P@ssword2';
        $this->assertTrue($this->validatePassword($prev_password, $new_password));
    }

    /** @test */
    public function validatePassword_same_password()
    {
        $prev_password = 'password';
        $new_password = 'password1';
        $this->assertFalse($this->validatePassword($prev_password, $new_password));
    }

    /** @test */
    public function validatePassword_no_capital_letters()
    {
        $prev_password = 'password';
        $new_password = 'password2';
        $this->assertFalse($this->validatePassword($prev_password, $new_password));
    }

    /** @test */
    public function validatePassword_no_special_characters()
    {
        $prev_password = 'password';
        $new_password = 'Password2';
        $this->assertFalse($this->validatePassword($prev_password, $new_password));
    }

    /** @test */
    public function validatePassword_short_password()
    {
        $prev_password = 'password';
        $new_password = 'P@sswor';
        $this->assertFalse($this->validatePassword($prev_password, $new_password));
    }

    /** @test */
    public function validatePassword_only_capital_letters_succesful()
    {
        $prev_password = 'password';
        $new_password = 'P@SSWORD';
        $this->assertTrue($this->validatePassword($prev_password, $new_password));
    }

    /** @test */
    public function validatePassword_only_special_characters_succesful()
    {
        $prev_password = 'password';
        $new_password = 'P@#$%^&*';
        $this->assertTrue($this->validatePassword($prev_password, $new_password));
    }

    private function validatePassword($prev_password, $new_password)
    {
        /**
         * Configuring Mocks
         */



        /* ----- Done configuring Mocks ----- */

        // Check if passwords are the same 
        if ($prev_password == $new_password) {
            return false;
        }

        // Check if new password complies to the given requirements

        // Length of password should be 8 characters or longer
        if (strlen($new_password) < 8) {
            return false;
        }

        // Password should contain atleast one capital letter
        if (!$this->isPartUppercase($new_password)) {
            return false;
        }

        // Password should contain atleast one special character
        if (!$this->hasSpecialCharacter($new_password)) {
            return false;
        }

        return true;
    }

    private function isPartUppercase($string)
    {
        return (bool) preg_match('/[A-Z]/', $string);
    }

    private function hasSpecialCharacter($string)
    {
        return (bool) preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string);
    }
}
