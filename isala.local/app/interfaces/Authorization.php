<?php

interface Authorization {
    // Require a valid User Session
    public function authorize($args);
}