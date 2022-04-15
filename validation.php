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

            $this->min = $this->getAttr("min")?? 5;
            $this->max = $this->getAttr("max")?? 30;
            $this->check = $this->getAttr("check")?? "text";
            $this->same = $this->getAttr("same-password");

            if ($this->check != "file") return;

            $this->size = $this->getAttr("size");
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

        function validate () {
            foreach ($this->patterns as $pattern) {
                if ($pattern->required && !$pattern->value)
                    return new Status(false, "input is empty");

                if (!$pattern->required && !$pattern->value) continue;

                $validate = $this->{$pattern->check}($pattern)?? new Status(true);
                $validate->message = $validate->message?? $pattern->name . " invalid";

                $sameTarget = [...array_filter($this->patterns, fn($item) => $item->name == $pattern->same)];
                $same = $pattern->same && $this->same($pattern->value, $sameTarget[0]->value);

                if ($same)
                    return $validate->status? new Status(false, "password and username is same"): $validate;

                if (!$validate->status) return $validate;
            }

            return new Status(true, "data is valid");
        }

        function __construct (&$data, $patterns) {
            foreach ($patterns as $pattern) {
                $name = substr($pattern, 0, strpos($pattern, " "));
                $data[$name] = trim($data[$name]);

                array_push($this->patterns, new Pattern($pattern, $name, $data[$name]));
            }

            $validate = $this->validate();

            $this->ok = $validate->status;
            $this->message = str_replace("-", " ", $validate->message);
        }

    }