<?php

    require_once "validation.php";

    $data = [
        "first-name" => "matin",
        "username" => "matinnez",
        "password" => "HxAic9@20#"
    ];

    $validate = new \Validation\Validate($data, [
        "username check=username required"
    ]);

    var_dump($validate->ok, $validate->message);

?>