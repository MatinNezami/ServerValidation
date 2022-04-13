<?php

    require_once "validation.php";
    use Validation;

    $data = [
        "first-name" => "matin",
        "username" => "matinnez",
        "password" => "HxAic9@20#"
    ];

    $validate = new \Validation\Validate($data, 
        "username check=username required"
    );

?>