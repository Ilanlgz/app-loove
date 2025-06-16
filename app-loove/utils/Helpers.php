<?php

class Helpers {
    // Function to sanitize user input
    public static function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // Function to generate a random string for unique identifiers
    public static function generateRandomString($length = 10) {
        return bin2hex(random_bytes($length));
    }

    // Function to validate email format
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Function to format date
    public static function formatDate($date) {
        return date("Y-m-d H:i:s", strtotime($date));
    }

    // Function to check if a value is empty
    public static function isEmpty($value) {
        return empty(trim($value));
    }
}