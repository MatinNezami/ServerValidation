<?php

    require_once "validation.php";

    $fileContent = base64_encode(file_get_contents("image.jpg"));
    $data = [
        "first-name" => "matin",
        "email" => "matin@email.com",
        "age" => "  19  ",
        "id" => "matin",
        "password" => "HXiJcoo!@9",
        "re-enter" => "HXiJcoo!@9",
        "url" => "http://www.w3.org?page=xmlns",
        "phone-number" => "+989901115289",
        "profile" => $fileContent
    ];

    $validate = new \Validation\Validate($data, [
        "first-name required min=2 check=text",
        "email required check=email",
        "id check=username required same-password=password",
        "age check=number min=18",
        "password check=password required",
        "url check=url",
        "phone-number required check=tel",
        "test check=email required",
        "re-enter required retype=password",
        "profile check=base64 mime=webp,jpeg min=10K requried"
    ]);


    $validate->add("iran zanjan", "location check=text required");
    $validate->add("HXiJcoo!@9", "user-retype retype=password required");
    

    // $file = new \Validation\Validate($_FILES, [
    //     "profile mime=webp,png,jpeg,svg min=10K max=10M required"
    // ]);

    echo $validate->message . "\n";

?>