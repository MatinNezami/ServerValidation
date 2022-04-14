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

    class Validate {
        public $ok, $message;
        private $patterns = [];

        function validate () {
            foreach ($this->patterns as $pattern) {
                if ($pattern->required && !$pattern->value) {
                    $this->ok = false;
                    $this->message = "input is empty";
                }

                if (!$pattern->required && !$pattern->value) continue;
            }
        }

        function __construct ($data, $patterns) {
            foreach ($patterns as $pattern) {
                $name = substr($pattern, 0, strpos($pattern, " "));

                array_push($this->patterns, new Pattern($pattern, $name, $data[$name]));
            }

            $this->validate();
        }

    }