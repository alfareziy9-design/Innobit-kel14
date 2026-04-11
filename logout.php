<?php
require_once 'functions/auth.php';

logoutUser();

header('Location: login.php');
exit;