<html>
<?php

// Initialize variables with default empty values to prevent undefined variable notices.
$fnt = '';

// Check if the GET parameters are provided and sanitize their values.
if (isset($_GET["fnt"])) {
    $fnt = htmlspecialchars($_GET["fnt"], ENT_QUOTES, 'UTF-8'); // Sanitize input.
}

echo "
<head>

<style type='text/css'>
@font-face { font-family: '$fnt';
src: url('/wp-content/uploads/fonts/$fnt/$fnt.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/$fnt/$fnt.woff') format('woff');
}

@font-face { font-family: 'BlankFallbackFont';
src: url('/wp-content/uploads/fonts/FallbackFont/AdobeBlank.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/FallbackFont/AdobeBlank.woff') format('woff');
}

@font-face { font-family: 'FallbackFont';
src: url('/wp-content/uploads/fonts/FallbackFont/UnicodeBMPFallback.woff2') format('woff2');
    url('/wp-content/uploads/fonts/FallbackFont/UnicodeBMPFallback.woff') format('woff');
}

.hebrew { font-family: '$fnt', 'FallbackFont', 'BlankFallbackFont';
	font-size: 1.4em;
	line-height: 1.35em;
	direction: rtl;
	text-align: right;
}

.yiddish { font-family: '$fnt', 'FallbackFont', 'BlankFallbackFont';
	font-size: 1.4em;
	line-height: 1.35em;
	direction: rtl;
	text-align: right;
}

.numbers { font-family: '$fnt', 'FallbackFont', 'BlankFallbackFont';
	font-size: 1em; 
	line-height: 1.35em;
	text-align: left;
	direction: ltr;
}

.punctuation { font-family: '$fnt', 'FallbackFont', 'BlankFallbackFont';
	font-size: 1em; 
	line-height: 1.35em;
	text-align: left;
	direction: ltr;
}

.symbols { font-family: '$fnt', 'FallbackFont', 'BlankFallbackFont';
	font-size: 1em; 
	line-height: 1.35em;
	text-align: left;
	direction: ltr;
}

.latin { font-family: '$fnt', 'FallbackFont', 'BlankFallbackFont';
	font-size: 1em; 
	line-height: 1.35em;
	text-align: left; 
	direction: ltr;
}	
</style>
</head>

<body style='background-color:orange;'>"; 

// Output the text (Hebrew script and Yiddish characters, Numerals, Punctuation, Symbols, and Latin script)
if (!empty($fnt)) {
    echo "<table><tr><td>";
        echo "<span class='numbers'>0 1 2 3 4 5 6 7 8 9 Ⅷ ∞</span><br />";   
        echo "<span class='punctuation'>“ ⹂ „ ” ⹝ ⸗ ‽ ; : ⸿ § ¶ ☜ ◦ · ◌</span><br />";
        echo "<span class='symbols'>⊙ ꙳ ✡ 🔯  🟌🖖 🧿 🕍 🕎︎ ☬ ֍ ֎ 🕮 🗀</span><br />";
        echo "<span class='latin'>Aa Bb Cc Dd Ee Ff Gg Hh Ḥḥ Ii Jj Kk Ll Mm Nn Oo Pp Qq Rr Ss Tt Uu Vv Ww Xx Yy Zz</span>";
    echo "</td><td>";  
        echo "<div class='hebrew'>א ﭏ ב ג ד ה ﬣ ו ז ח ט י ׯ כ כׇ ך ל מ־ם נ ׆ ן ס ע פ ף צ ץ ק ר ש ת ׃</div>";
        echo "<div class='yiddish'>אַ אָ װ ױ ײ ײַ בֿ כֿ פֿ</div>"; 
    echo "</td></tr></table>";
}

echo "</body>";
?>

</html>