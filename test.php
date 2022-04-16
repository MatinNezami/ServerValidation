<?php

    require_once "validation.php";

    $data = [
        "first-name" => "matin",
        "email" => "matin@email.com",
        "age" => "  19  ",
        "id" => "matin",
        "password" => "HXiJcoo!@9",
        "re-enter" => "HXiJcoo!@9",
        "location" => "http://www.w3.org?page=xmlns",
        "phone-number" => "+989901115289"
    ];

    $validate = new \Validation\Validate($data, [
        "first-name required min=2 check=text",
        "email required check=email",
        "id check=username required same-password=password",
        "age check=number min=18",
        "password check=password required retype-reference",
        "location check=url",
        "phone-number required check=tel",
        "re-enter required check=retype"
    ]);

    $validate->add("Xhi@d 9c ;3@", "check=password required");

    echo $validate->message . "\n";

?>