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
            $min = $this->getAttr("min");

            $this->min = $min?? 5;
            $this->max = $max?? 30;
            $this->retype = $this->getAttr("retype");
            $this->check = $this->getAttr("check")?? "text";

            $this->same = $this->getAttr("same-password");

            if ($this->check != "file" && $this->check != "base64") return;

            $this->max = $max?? "10G";
            $this->min = $min?? "1K";
            $this->mime = $this->getAttr("mime");
        }

    }

    class Validate {
        public $ok, $message;
        private $patterns = [];

        function text ($pattern) {
            $this->setStatus(
                preg_match("/^.{" . $pattern->min . "," . $pattern->max . "}$/", $pattern->value),
                $pattern
            );
        }

        function email ($pattern) {
            $this->setStatus(
                filter_var($pattern->value, FILTER_VALIDATE_EMAIL), $pattern
            );
        }

        function number ($pattern) {
            if (!(+$pattern->value >= $pattern->min && +$pattern->value <= $pattern->max))
                $this->setStatus(false, "number out of range");
        }

        function username ($pattern) {
            $this->setStatus(
                preg_match("/^(?=.{" . $pattern->min . "," . $pattern->max . "}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/",
                    $pattern->value
                ),

                $pattern
            );
        }

        function password ($pattern) {
            $this->setStatus(
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
            $this->setStatus(
                filter_var($pattern->value, FILTER_VALIDATE_URL), $pattern
            );
        }

        function tel ($pattern) {
            $this->setStatus(
                preg_match("/^\+\d{12}$/", $pattern->value), $pattern
            );
        }

        function retype ($pattern) {
            $reference = [...array_filter(
                $this->patterns, fn($item) => $item->name == $pattern->retype
            )];

            if (!isset($reference[0])) return;

            $equal = $pattern->value == $reference[0]->value;

            $this->setStatus($equal, $equal? "data is valid": "conferm password");
        }

        function fileSize (...$sizes) {
            foreach ($sizes as $size)
                yield str_replace("K", "000", str_replace("M", "000000", str_replace("G", "000000000", $size)));
        }

        function file ($pattern) {
            $has = NULL;

            foreach (explode(",", $pattern->mime) as $mime)
                if (str_contains($pattern->value["type"], str_replace(",", "", $mime)))
                    $has = true;

            if (!$has) return $this->setStatus(false, "upload file type invalid");

            $sizes = $this->fileSize($pattern->min, $pattern->max);
            $min = $sizes->current();
            $sizes->next();

            if ($pattern->value["size"] < $min)
                return $this->setStatus(false, "upload file is small");

            if ($pattern->value["size"] > $sizes->current())
                return $this->setStatus(false, "upload file is big");
        }

        function base64 ($pattern) {
            $has = NULL;
            $bin = base64_decode($pattern->value);
            $type = finfo_buffer(finfo_open(), $bin, FILEINFO_MIME_TYPE);

            foreach (explode(",", $pattern->mime) as $mime)
                if (str_contains($type, str_replace(",", "", $mime))) $has = true;

            if (!$has) return $this->setStatus(false, "upload file type invalid");

            $size = strlen($bin);
            $sizes = $this->fileSize($pattern->min, $pattern->max);
            $min = $sizes->current();
            $sizes->next();

            if ($size < $min)
                return $this->setStatus(false, "upload file is small");

            if ($size > $sizes->current())
                return $this->setStatus(false, "upload file is big");
        }

        function setStatus ($status, $message) {
            $this->ok = $status;
            $this->message = $message instanceof Pattern? $message->name . " invalid": $message;
        }

        function add ($value, $pattern) {
            $this->validate([new Pattern($pattern, $this->getName($pattern), $value)]);
            $this->isValid();
        }

        function validate ($patterns) {
            if (!$this->ok and $this->ok !== NULL)return;
            
            foreach ($patterns as $pattern) {
                if ($pattern->required && !$pattern->value)
                    return $this->setStatus(false, "input is empty");

                if (!$pattern->required && !$pattern->value) continue;

                if ($pattern->retype)
                    return $this->retype($pattern);

                $this->{$pattern->check}($pattern);

                if (!$this->ok) break;

                $sameTarget = [...array_filter($this->patterns, fn($item) => $item->name == $pattern->same)];
                $same = $pattern->same && $sameTarget && $this->same($pattern->value, $sameTarget[0]->value);

                if ($same) return $this->setStatus(false, "password and username is same");
            }
        }

        function isValid () {
            if ($this->ok) $this->message = "data is valid";
            $this->message = str_replace("-", " ", $this->message);
        }

        function getName ($pattern) {
            return substr($pattern, 0, strpos($pattern, " "));
        }

        function __construct (&$data, $patterns) {
            foreach ($patterns as $pattern) {
                $name = $this->getName($pattern);

                if (!isset($data[$name])) continue;

                if (is_string($data[$name]))
                    $data[$name] = trim($data[$name]);

                array_push($this->patterns, new Pattern($pattern, $name, $data[$name]));
            }

            $this->validate($this->patterns);
            $this->isValid();
        }
    }