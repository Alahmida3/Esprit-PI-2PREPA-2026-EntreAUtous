<?php
session_start();
session_destroy();

header("Location: ../views/front/login.php");
exit;