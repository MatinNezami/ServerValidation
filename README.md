PHP Validation From Library
===========================

You can validation forms with create instance from `Validate` class, from `Validation` namespace

## Usage


#### PHP Instance

```php
$validate = new \Validation\Validate($_POST, [
    "first-name required min=2 check=text",
    "email required check=email",
    "id check=username required same-password=password",
    "password check=password required retype-reference",
    "re-enter required check=retype"
]);

if (!$validate->ok)
    die($validate->message);

echo $validate->message;
```

`validate->ok` porperty is form validation status and `validate->message` propery is status message


#### Patterns

Patterns array is parameter `Validate` class constructor function:

* Required or optional: if exists `required` directive in pattern validation required
* Check with types: `check` directive value is validation type


#### Validation Files

`profile mime=webp,png,jpeg min=10K max=10M`

`mime` directive for upload file type: `image`, `video`, `mpeg` and more types  
`max` and `min` directive for size range upload file: `100K`, `10G` and more sizes

You can insert multiple type `mime="svg, video"`  
Warning: you can't use float number in sizes, for example `1.5G`  
Tip: default `max` directive value is `10G` and `min` directive value is `1K`


#### Validation Encode Files (Base64)

If you need validation base64 files, you can use `base64` type  
you can use `max`, `min` and `mime` directive, for exaple:

```php
$validate = new \Validation\Validate($_POST, [
    "profile check=base64 min=1M max=10M required"
]);
```


## Tips


#### Add Other Values

If validation other values, you can use `add` method:

```php
$validation = new \Validation\Validate($_POST, [
    "password check=password required"
]);

$validation->add($_GET["id"], "required check=username");
$validation->add($_POST["retype"], "required retype=password");
```

First parameter is value and last parameter is pattern


#### Typs

* username
* retype
* password
* file
* url
* tel
* text
* email
* number
* base64

Warning you can only use `retype`, type in `add` method,
so use in value pattern set `retype` directive


#### Same Password With Username

If you need validation same password with username, you can use `same-password` directive and 
assign target input name, validation this values for same, for exaple:

```php
$validation = new \Validation\Validate($_POST, [
    "password check=password required",
    "id check=username required same-password=password"
]);
```


#### Conferm Password

If you need check value equal with outer values (conferm) use `retype` directive,
for example:

```php
$validation = new \Validation\Validate($_POST, [
    "passwd required check=password",
    "conferm required retype=passwd"
]);
```
Use in `add` method:

```php
$validation->add("HcI!e 9$4i  ", "required retype=passwd");
```


#### Trim

validation inputs value trim: remove start and end white space, except
password validation (`password`, `retype`)


For better understand run and read `test.php`