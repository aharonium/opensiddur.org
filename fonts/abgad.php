<html>
<?php

// Initialize variables with default empty values to prevent undefined variable notices.
$heb = '';
$lat = '';

// Check if the GET parameters are provided and sanitize their values.
if (isset($_GET["heb"])) {
    $heb = htmlspecialchars($_GET["heb"], ENT_QUOTES, 'UTF-8'); // Sanitize input.
}

if (isset($_GET["lat"])) {
    $lat = htmlspecialchars($_GET["lat"], ENT_QUOTES, 'UTF-8'); // Sanitize input.
}


echo "
<head>

<style type='text/css'>
@font-face { font-family: '$heb';
src: url('/wp-content/uploads/fonts/$heb/$heb.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/$heb/$heb.woff') format('woff');
}

@font-face { font-family: 'BlankFallbackFont';
src: url('/wp-content/uploads/fonts/FallbackFont/AdobeBlank.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/FallbackFont/AdobeBlank.woff') format('woff');
}

.hebrew { font-family: '$heb', 'BlankFallbackFont';
	font-size: 1.4em;
	line-height: 1.35em;
	direction: rtl;
	text-align: right;
}

.numbers { font-family: '$heb', 'BlankFallbackFont';
	font-size: 1em; 
	line-height: 1.35em;
	text-align: left;
	direction: ltr;
}

.latin { font-family: '$heb', 'BlankFallbackFont';
	font-size: 1em; 
	line-height: 1.35em;
	text-align: left; 
	direction: ltr;
}	
</style>
</head>

<body style='background-color:orange;'>"; 

// Output the Hebrew text if the 'heb' parameter is set.
if (!empty($heb)) {
    echo "<div class='hebrew'>א ﭏ ב ג ד ה ﬣ ו ז ח ט י ײַ כ&nbsp;ך ל מ&nbsp;ם נ&nbsp;ן ס ע פ&nbsp;ף צ&nbsp;ץ ק ר ש ת ׃</div>";
    echo "<span class='numbers'>0 1 2 3 4 5 6 7 8 9 ∞ . ‽ ; : § ✡ ☜</span><br />";
}

// Output the Latin text if the 'lat' parameter is set.
if (!empty($heb)) {
    echo "<span class='latin'>Aa Bb Cc Dd Ee Ff Gg Hh Ḥḥ Ii Jj Kk Ll Mm Nn Oo Pp Qq Rr Ss Tt Uu Vv Ww Xx Yy Zz</span>";
}

echo "</body>";
?>

</html>