<?php

    require_once "validation.php";

    $data = [
        "first-name" => "matin",
        "email" => "matin@gmail.com",
        "age" => "  19  ",
        "username" => "matinnez",
        "password" => "HxAic9@20#"
    ];

    $validate = new \Validation\Validate($data, [
        "first-name required min=2 check=text",
        "email required check=email",
        "age check=number min=18"
    ]);

    echo $validate->message . "\n";

?>