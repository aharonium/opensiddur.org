<html>

<?php

// Initialize $umid with a default value to prevent undefined variable notices.
$umid = '';

// Check if the 'nameid' parameter is passed via GET and sanitize its value.
if (isset($_GET["nameid"])) {
    $umid = htmlspecialchars($_GET["nameid"], ENT_QUOTES, 'UTF-8'); // Sanitize to prevent XSS attacks.
}

echo "
<head>
   <style>
 a:link {
    text-decoration: none;
    color: #000000;
}

a:visited {
    text-decoration: none;
    color: #000000;
}

a:hover { 
    text-decoration: none;
    color: blue;
}

a:active {
    text-decoration: none;
    color: #000000;
}
  </style>
</head>

<body>"; 

// Display $umid safely
echo htmlspecialchars($umid, ENT_QUOTES, 'UTF-8');

if (!empty($umid)) {
    // Use $umid inside the shortcode, ensuring it is properly interpolated.
    echo do_shortcode('[display-posts author_id="' . $umid . '"]');
}

echo "</body>";

?>

</html>