<?php

class Validator {
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword($password) {
        return strlen($password) >= 8;
    }

    public static function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }

    public static function validateDateOfBirth($dob) {
        $date = DateTime::createFromFormat('Y-m-d', $dob);
        return $date && $date->format('Y-m-d') === $dob && $date < new DateTime();
    }

    public static function validateProfilePicture($file) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        return in_array($file['type'], $allowedMimeTypes) && $file['size'] <= 2000000; // 2MB limit
    }

    public static function validatePreferences($preferences) {
        return is_array($preferences) && !empty($preferences);
    }
}