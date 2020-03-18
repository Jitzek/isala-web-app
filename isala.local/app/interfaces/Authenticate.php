<?php

interface Authenticate {
    public function authenticate($uid, $token);
}