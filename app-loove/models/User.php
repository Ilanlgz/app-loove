<?php

class User {
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $password;
    private $gender;
    private $sexualOrientation;
    private $dateOfBirth;
    private $profilePicture;
    private $preferences;
    private $relationshipType;
    private $interests;

    public function __construct($firstName, $lastName, $email, $password, $gender, $sexualOrientation, $dateOfBirth, $profilePicture) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->gender = $gender;
        $this->sexualOrientation = $sexualOrientation;
        $this->dateOfBirth = $dateOfBirth;
        $this->profilePicture = $profilePicture;
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getEmail() {
        return $this->email;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    public function setPreferences($preferences) {
        $this->preferences = $preferences;
    }

    public function setRelationshipType($relationshipType) {
        $this->relationshipType = $relationshipType;
    }

    public function setInterests($interests) {
        $this->interests = $interests;
    }

    public function updateProfile($firstName, $lastName, $email, $profilePicture) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->profilePicture = $profilePicture;
    }

    public function getProfile() {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'profilePicture' => $this->profilePicture,
            'preferences' => $this->preferences,
            'relationshipType' => $this->relationshipType,
            'interests' => $this->interests,
        ];
    }
}