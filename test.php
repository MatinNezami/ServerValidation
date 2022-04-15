<?php

    require_once "validation.php";

    $data = [
        "first-name" => "matin",
        "email" => "matin@gmail.com",
        "age" => "  19  ",
        "id" => "matin",
        "password" => "HxAic9@20#"
    ];

    $validate = new \Validation\Validate($data, [
        "first-name required min=2 check=text",
        "email required check=email",
        "age check=number min=18",
        "id check=username required"
    ]);

    echo $validate->message . "\n";

?>