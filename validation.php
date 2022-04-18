<?php

    namespace Validation;

    class Pattern {
        private $pattern;

        private function getAttr ($attr) {
            if (!str_contains($this->pattern, $attr)) return;

            preg_match("/" . $attr . "=[^\s]*/", $this->pattern, $matches);

            return substr($matches[0], strpos($matches[0], "=") + 1);
        }

        function __construct ($pattern, $name, $value) {
            $this->value = $value;
            $this->pattern = $pattern;
            $this->name = $name;

            $this->required = str_contains($pattern, "required");

            $max = $this->getAttr("max");

            $this->min = $this->getAttr("min")?? 5;
            $this->max = $max?? 30;
            $this->retype = $this->getAttr("retype");
            $this->check = $this->getAttr("check")?? "text";

            $this->same = $this->getAttr("same-password");

            if ($this->check != "file") return;

            $this->max = $max?? 10_000_000_000;
            $this->mime = $this->getAttr("mime");
        }

    }

    class Status {
        function __construct ($status, $message = NULL) {
            $this->status = $status;
            $this->message = $message;
        }
    }

    class Validate {
        public $ok, $message;
        private $patterns = [];

        function text ($pattern) {
            return new Status(
                preg_match("/^.{" . $pattern->min . "," . $pattern->max . "}$/", $pattern->value)
            );
        }

        function email ($pattern) {
            return new Status(
                filter_var($pattern->value, FILTER_VALIDATE_EMAIL)
            );
        }

        function number ($pattern) {
            if (!(+$pattern->value >= $pattern->min && +$pattern->value <= $pattern->max))
                return new Status(false, "number out of range");
        }

        function username ($pattern) {
            return new Status(
                preg_match("/^(?=.{" . $pattern->min . "," . $pattern->max . "}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/",
                    $pattern->value
                )
            );
        }

        function password ($pattern) {
            return new Status(
                preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{" . $pattern->min . "," . $pattern->max . "}$/",
                    $pattern->value
                ),

                "password isn't strong"
            );
        }

        function same ($password, $username) {
            foreach (str_split(strtolower($password), 3) as $item)
                if (str_contains(strtolower($username), $item)) return true;
        }

        function url ($pattern) {
            return new Status(
                filter_var($pattern->value, FILTER_VALIDATE_URL)
            );
        }

        function tel ($pattern) {
            return new Status(
                preg_match("/^\+\d{12}$/", $pattern->value)
            );
        }

        function retype ($pattern) {
            $reference = [...array_filter(
                $this->patterns, fn($item) => $item->name == $pattern->retype
            )];

            if (!isset($reference[0])) return;

            return new Status(
                $pattern->value == $reference[0]->value,
                "conferm password"
            );
        }

        function fileSize (...$sizes) {
            foreach ($sizes as $size)
                yield str_replace("K", "000", str_replace("M", "000000", str_replace("G", "000000000", $size)));
        }

        function file ($pattern) {
            $has;

            foreach (explode(",", $pattern->mime) as $mime)
                if (str_contains($pattern->value["type"], str_replace(",", "", $mime)))
                    $has = true;

            if (!$has) return new Status (false, "upload file type invalid");

            $sizes = $this->fileSize($pattern->min, $pattern->max);
            $min = $sizes->current();
            $sizes->next();

            if ($pattern->value["size"] < $min)
                return new Status(false, "upload file is small");

            if ($pattern->value["size"] > $sizes->current())
                return new Status(false, "upload file is big");
        }

        function message ($pattern, $validate) {
            return $validate->message?? $pattern->name . " invalid";
        }

        function setStatus ($status, $message) {
            $this->status = $status;
            $this->message = $message;
        }

        function add ($value, $pattern) {
            $pattern = new Pattern($pattern, "", $value);

            if (!$this->ok || !$pattern->required && !$value) return;

            if ($pattern->required && !$value)
                return $this->setStatus(false, "input is empty");

            if ($pattern->retype && !($this->retype($pattern)?? new Status(true))->status)
                return $this->setStatus(false, "conferm password");
    
            $validate = $this->{$pattern->check}($pattern);
                
            if ($validate->status) return;

            $this->setStatus(false, $this->message($pattern, $validate));
        }

        function validate () {
            $ok = new Status(true);

            foreach ($this->patterns as $pattern) {
                if ($pattern->required && !$pattern->value)
                    return new Status(false, "input is empty");

                if (!$pattern->required && !$pattern->value) continue;

                if ($pattern->retype)
                    return $this->retype($pattern)?? $ok;

                $validate = $this->{$pattern->check}($pattern)?? $ok;
                $validate->message = $this->message($pattern, $validate);

                $sameTarget = [...array_filter($this->patterns, fn($item) => $item->name == $pattern->same)];
                $same = $pattern->same && $this->same($pattern->value, $sameTarget[0]->value);

                if ($same)
                    return $validate->status? new Status(false, "password and username is same"): $validate;

                if (!$validate->status) return $validate;
            }

            return $ok;
        }

        function __construct (&$data, $patterns) {
            foreach ($patterns as $pattern) {
                $name = substr($pattern, 0, strpos($pattern, " "));

                if (is_string($data[$name]))
                    $data[$name] = trim($data[$name]);

                array_push($this->patterns, new Pattern($pattern, $name, $data[$name]));
            }

            $validate = $this->validate();

            $this->ok = $validate->status;
            $this->message = str_replace("-", " ", $this->ok? "data is valid": $validate->message);
        }
    }