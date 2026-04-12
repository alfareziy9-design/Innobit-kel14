<?php
require_once 'functions/auth.php';

logoutUser();

header('Location: index.php');
exit;