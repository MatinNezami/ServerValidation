<?php

    require_once "validation.php";

    $data = [
        "first-name" => "matin",
        "email" => "matin@gmail.com",
        "username" => "matinnez",
        "password" => "HxAic9@20#"
    ];

    $validate = new \Validation\Validate($data, [
        "first-name required min=2 check=text",
        "email required check=email"
    ]);

    echo $validate->message . "\n";

?>