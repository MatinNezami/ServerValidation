<?php

    require_once "validation.php";

    $data = [
        "first-name" => "matin",
        "username" => "matinnez",
        "password" => "HxAic9@20#"
    ];

    $validate = new \Validation\Validate($data, [
        "first-name required min=2 check=text"
    ]);

    if (!$validate->ok)
        die($validate->message . "\n");

    else echo "data is valid\n";

?>