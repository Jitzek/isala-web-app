<?php

interface Authentication {
    // Require a valid User Session
    public function authenticate();
}