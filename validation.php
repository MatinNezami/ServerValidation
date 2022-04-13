<?php

class Validate {

    function __construct ($data, $patterns) {
        $this->patterns = $patterns;
        $this->data = $data;
    }

}