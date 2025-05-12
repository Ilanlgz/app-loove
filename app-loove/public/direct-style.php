<?php
// This file outputs the style.css content directly for testing
header('Content-Type: text/css');
readfile(__DIR__ . '/css/style.css');
