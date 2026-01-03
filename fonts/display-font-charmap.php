<html>
<?php
// Initialize $fnt to a default value
$fnt = '';

// Check if the 'fnt' parameter is set in the GET request
if (isset($_GET["fnt"])) {
    $fnt = $_GET["fnt"];
}

if (!empty($fnt)) {     // Safe to use $fnt
    
echo "
<head>
<title>Unicode Hebrew Support & Diacritic Positioning in $fnt</title>
<style type='text/css'>
@font-face { font-family: '$fnt';
src: url('/wp-content/uploads/fonts/$fnt/$fnt.woff2') format('woff2'),  
    url('/wp-content/uploads/fonts/$fnt/$fnt.woff') format('woff');
}

@font-face { font-family: 'FallbackFont';
src: url('/wp-content/uploads/fonts/FallbackFont/UnicodeBMPFallback.woff2') format('woff2');
    url('/wp-content/uploads/fonts/FallbackFont/UnicodeBMPFallback.woff') format('woff');
}

@font-face { font-family: 'SymbolReference';
src: url('/wp-content/uploads/fonts/FreeSerif/FreeSerif.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/FreeSerif/FreeSerif.woff') format('woff');
}

@font-face { font-family: 'HebrewReference';
src: url('/wp-content/uploads/fonts/SBL-Hebrew/SBL-Hebrew.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/SBL-Hebrew/SBL-Hebrew.woff') format('woff');
}

@font-face { font-family: 'Hebrew-Samaritan';
src: url('/wp-content/uploads/fonts/Hebrew-Samaritan/Hebrew-Samaritan.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/Hebrew-Samaritan/Hebrew-Samaritan.woff') format('woff');
}

.font { font-family: '$fnt', 'FallbackFont';
 direction: rtl;
 text-align: right;
 line-height: 1.5em;
 font-size: 2em; }
 
body {
    background-color: #cccccc;
}

a:link {
    text-decoration: none;
	color: #000000;
}

a:visited {
    text-decoration: none;
	color: blue;
}

a:hover { font-family: 'SymbolReference';
    text-decoration: none;
	color: blue;
}

a:active {
    text-decoration: none;
	color: #000000;
}

a.ex4:hover, a.ex4:active, a.ex4:visited {font-family: 'HebrewReference';}
a.ex5:hover, a.ex5:active, a.ex5:visited {font-family: 'Hebrew-Samaritan';}

table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 10px;
  margin-left: auto;
  margin-right: auto;
  background-color: #eeeeee;
}

h1, h2, h3 { font-family: sans-serif;
 font-family: sans-serif;
 text-align: center;
 direction: ltr;
}

.vanilla { font-family: 'Helvetica','Georgia','Calibri',sans-serif; direction: ltr; font-size: 0.8em; }
 </style>
</head>

<body>
<h1>Unicode Hebrew Support & Diacritic Positioning</h1>
<p /><br />
<h1>$fnt</h1>

<table><tr><td><div class='font' style='text-align: center; font-size: 4em;'>
א ב ג ד ה ו ז ח ט 
י כ&nbsp;ך ל מ&nbsp;ם נ&nbsp;ן ס ע פ&nbsp;ף צ&nbsp;ץ 
ק ר ש ת 
</div></td></tr>
</table>
<p /><br />
<div class='vanilla' style='text-align: center; font-size: 1em;'>
<a href='/wp-content/uploads/fonts/$fnt/$fnt.zip'>download $fnt.zip</a>
<p /><br />

Note: Unsupported characters and diacritics in $fnt are represented by a <a href='https://en.wikipedia.org/wiki/Fallback_font'>Fallback Font</a>.
<hr />
</div>

<div class='font'>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover for reference implementation in SBL Hebrew.</div>
<table>
<tr><td><div class='font'><a class= 'ex4'>א</a></div></td><td><div class='font'><a class= 'ex4'>אְ</a></div></td><td><div class='font'><a class= 'ex4'>אֱ</a></div></td><td><div class='font'><a class= 'ex4'>אֲ</a></div></td><td><div class='font'><a class= 'ex4'>אֳ</a></div></td><td><div class='font'><a class= 'ex4'>אִ</a></div></td><td><div class='font'><a class= 'ex4'>אֵ</a></div></td><td><div class='font'><a class= 'ex4'>אֶ</a></div></td><td><div class='font'><a class= 'ex4'>אַ</a></div></td><td><div class='font'><a class= 'ex4'>אָ</a></div></td><td><div class='font'><a class= 'ex4'>אׇ</a></div></td><td><div class='font'><a class= 'ex4'>אֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>אֻ</a></div></td><td><div class='font'><a class= 'ex4'>אּ</a></div></td><td><div class='font'><a class= 'ex4'>אֽ</a></div></td><td><div class='font'><a class= 'ex4'>אֿ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>אַ</a></div></td><td><div class='font'><a class= 'ex4'>אָ</a></div></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﭏ</a></div></td><td><div class='font'><a class= 'ex4'>ﬡ</a></div></td><td><div class='font'><a class= 'ex4'>א֯</a></div></td><td><div class='font'><a class= 'ex4'>אׄ</a></div></td><td><div class='font'><a class= 'ex4'>אׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ב</a></div></td><td><div class='font'><a class= 'ex4'>בְ</a></div></td><td><div class='font'><a class= 'ex4'>בֱ</a></div></td><td><div class='font'><a class= 'ex4'>בֲ</a></div></td><td><div class='font'><a class= 'ex4'>בֳ</a></div></td><td><div class='font'><a class= 'ex4'>בִ</a></div></td><td><div class='font'><a class= 'ex4'>בֵ</a></div></td><td><div class='font'><a class= 'ex4'>בֶ</a></div></td><td><div class='font'><a class= 'ex4'>בַ</a></div></td><td><div class='font'><a class= 'ex4'>בָ</a></div></td><td><div class='font'><a class= 'ex4'>בׇ</a></div></td><td><div class='font'><a class= 'ex4'>בֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>בֻ</a></div></td><td><div class='font'><a class= 'ex4'>בּ</a></div></td><td><div class='font'><a class= 'ex4'>בֽ</a></div></td><td><div class='font'><a class= 'ex4'>בֿ</a></div></td><td><div class='font'><a class= 'ex4'>בﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ב֯</a></div></td><td><div class='font'><a class= 'ex4'>בׄ</a></div></td><td><div class='font'><a class= 'ex4'>בׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ג</a></div></td><td><div class='font'><a class= 'ex4'>גְ</a></div></td><td><div class='font'><a class= 'ex4'>גֱ</a></div></td><td><div class='font'><a class= 'ex4'>גֲ</a></div></td><td><div class='font'><a class= 'ex4'>גֳ</a></div></td><td><div class='font'><a class= 'ex4'>גִ</a></div></td><td><div class='font'><a class= 'ex4'>גֵ</a></div></td><td><div class='font'><a class= 'ex4'>גֶ</a></div></td><td><div class='font'><a class= 'ex4'>גַ</a></div></td><td><div class='font'><a class= 'ex4'>גָ</a></div></td><td><div class='font'><a class= 'ex4'>גׇ</a></div></td><td><div class='font'><a class= 'ex4'>גֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>גֻ</a></div></td><td><div class='font'><a class= 'ex4'>גּ</a></div></td><td><div class='font'><a class= 'ex4'>גֽ</a></div></td><td><div class='font'><a class= 'ex4'>גֿ</a></div></td><td><div class='font'><a class= 'ex4'>גﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ג֯</a></div></td><td><div class='font'><a class= 'ex4'>גׄ</a></div></td><td><div class='font'><a class= 'ex4'>גׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ד</a></div></td><td><div class='font'><a class= 'ex4'>דְ</a></div></td><td><div class='font'><a class= 'ex4'>דֱ</a></div></td><td><div class='font'><a class= 'ex4'>דֲ</a></div></td><td><div class='font'><a class= 'ex4'>דֳ</a></div></td><td><div class='font'><a class= 'ex4'>דִ</a></div></td><td><div class='font'><a class= 'ex4'>דֵ</a></div></td><td><div class='font'><a class= 'ex4'>דֶ</a></div></td><td><div class='font'><a class= 'ex4'>דַ</a></div></td><td><div class='font'><a class= 'ex4'>דָ</a></div></td><td><div class='font'><a class= 'ex4'>דׇ</a></div></td><td><div class='font'><a class= 'ex4'>דֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>דֻ</a></div></td><td><div class='font'><a class= 'ex4'>דּ</a></div></td><td><div class='font'><a class= 'ex4'>דֽ</a></div></td><td><div class='font'><a class= 'ex4'>דֿ</a></div></td><td><div class='font'><a class= 'ex4'>דﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬢ</a></div></td><td><div class='font'><a class= 'ex4'>ד֯</a></div></td><td><div class='font'><a class= 'ex4'>דׄ</a></div></td><td><div class='font'><a class= 'ex4'>דׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ה</a></div></td><td><div class='font'><a class= 'ex4'>הְ</a></div></td><td><div class='font'><a class= 'ex4'>הֱ</a></div></td><td><div class='font'><a class= 'ex4'>הֲ</a></div></td><td><div class='font'><a class= 'ex4'>הֳ</a></div></td><td><div class='font'><a class= 'ex4'>הִ</a></div></td><td><div class='font'><a class= 'ex4'>הֵ</a></div></td><td><div class='font'><a class= 'ex4'>הֶ</a></div></td><td><div class='font'><a class= 'ex4'>הַ</a></div></td><td><div class='font'><a class= 'ex4'>הָ</a></div></td><td><div class='font'><a class= 'ex4'>הׇ</a></div></td><td><div class='font'><a class= 'ex4'>הֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>הֻ</a></div></td><td><div class='font'><a class= 'ex4'>הּ</a></div></td><td><div class='font'><a class= 'ex4'>הֽ</a></div></td><td><div class='font'><a class= 'ex4'>הֿ</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ה׳</a></div></td><td></td><td><div class='font'><a class= 'ex4'>ﬣ</a></div></td><td><div class='font'><a class= 'ex4'>ה֯</a></div></td><td><div class='font'><a class= 'ex4'>הׄ</a></div></td><td><div class='font'><a class= 'ex4'>הׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ו</a></div></td><td><div class='font'><a class= 'ex4'>וְ</a></div></td><td><div class='font'><a class= 'ex4'>וֱ</a></div></td><td><div class='font'><a class= 'ex4'>וֲ</a></div></td><td><div class='font'><a class= 'ex4'>וֳ</a></div></td><td><div class='font'><a class= 'ex4'>וִ</a></div></td><td><div class='font'><a class= 'ex4'>וֵ</a></div></td><td><div class='font'><a class= 'ex4'>וֶ</a></div></td><td><div class='font'><a class= 'ex4'>וַ</a></div></td><td><div class='font'><a class= 'ex4'>וָ</a></div></td><td><div class='font'><a class= 'ex4'>וׇ</a></div></td><td><div class='font'><a class= 'ex4'>וֹ</a></div></td><td><div class='font'><a class= 'ex4'>וֺ</a></div></td><td><div class='font'><a class= 'ex4'>וֻ</a></div></td><td><div class='font'><a class= 'ex4'>וּ</a></div></td><td><div class='font'><a class= 'ex4'>וֽ</a></div></td><td><div class='font'><a class= 'ex4'>וֿ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>װ</a></div></td><td><div class='font'><a class= 'ex4'>ױ</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ו֯</a></div></td><td><div class='font'><a class= 'ex4'>וׄ</a></div></td><td><div class='font'><a class= 'ex4'>וׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ז</a></div></td><td><div class='font'><a class= 'ex4'>זְ</a></div></td><td><div class='font'><a class= 'ex4'>זֱ</a></div></td><td><div class='font'><a class= 'ex4'>זֲ</a></div></td><td><div class='font'><a class= 'ex4'>זֳ</a></div></td><td><div class='font'><a class= 'ex4'>זִ</a></div></td><td><div class='font'><a class= 'ex4'>זֵ</a></div></td><td><div class='font'><a class= 'ex4'>זֶ</a></div></td><td><div class='font'><a class= 'ex4'>זַ</a></div></td><td><div class='font'><a class= 'ex4'>זָ</a></div></td><td><div class='font'><a class= 'ex4'>זׇ</a></div></td><td><div class='font'><a class= 'ex4'>זֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>זֻ</a></div></td><td><div class='font'><a class= 'ex4'>זּ</a></div></td><td><div class='font'><a class= 'ex4'>זֽ</a></div></td><td><div class='font'><a class= 'ex4'>זֿ</a></div></td><td><div class='font'><a class= 'ex4'>זﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ז֯</a></div></td><td><div class='font'><a class= 'ex4'>זׄ</a></div></td><td><div class='font'><a class= 'ex4'>זׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ח</a></div></td><td><div class='font'><a class= 'ex4'>חְ</a></div></td><td><div class='font'><a class= 'ex4'>חֱ</a></div></td><td><div class='font'><a class= 'ex4'>חֲ</a></div></td><td><div class='font'><a class= 'ex4'>חֳ</a></div></td><td><div class='font'><a class= 'ex4'>חִ</a></div></td><td><div class='font'><a class= 'ex4'>חֵ</a></div></td><td><div class='font'><a class= 'ex4'>חֶ</a></div></td><td><div class='font'><a class= 'ex4'>חַ</a></div></td><td><div class='font'><a class= 'ex4'>חָ</a></div></td><td><div class='font'><a class= 'ex4'>חׇ</a></div></td><td><div class='font'><a class= 'ex4'>חֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>חֻ</a></div></td><td><div class='font'><a class= 'ex4'>חּ</a></div></td><td><div class='font'><a class= 'ex4'>חֽ</a></div></td><td><div class='font'><a class= 'ex4'>חֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ח֯</a></div></td><td><div class='font'><a class= 'ex4'>חׄ</a></div></td><td><div class='font'><a class= 'ex4'>חׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ט</a></div></td><td><div class='font'><a class= 'ex4'>טְ</a></div></td><td><div class='font'><a class= 'ex4'>טֱ</a></div></td><td><div class='font'><a class= 'ex4'>טֲ</a></div></td><td><div class='font'><a class= 'ex4'>טֳ</a></div></td><td><div class='font'><a class= 'ex4'>טִ</a></div></td><td><div class='font'><a class= 'ex4'>טֵ</a></div></td><td><div class='font'><a class= 'ex4'>טֶ</a></div></td><td><div class='font'><a class= 'ex4'>טַ</a></div></td><td><div class='font'><a class= 'ex4'>טָ</a></div></td><td><div class='font'><a class= 'ex4'>טׇ</a></div></td><td><div class='font'><a class= 'ex4'>טֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>טֻ</a></div></td><td><div class='font'><a class= 'ex4'>טּ</a></div></td><td><div class='font'><a class= 'ex4'>טֽ</a></div></td><td><div class='font'><a class= 'ex4'>טֿ</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ט״ו</a></div></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ט֯</a></div></td><td><div class='font'><a class= 'ex4'>טׄ</a></div></td><td><div class='font'><a class= 'ex4'>טׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>י</a></div></td><td><div class='font'><a class= 'ex4'>יְ</a></div></td><td><div class='font'><a class= 'ex4'>יֱ</a></div></td><td><div class='font'><a class= 'ex4'>יֲ</a></div></td><td><div class='font'><a class= 'ex4'>יֳ</a></div></td><td><div class='font'><a class= 'ex4'>יִ</a></div></td><td><div class='font'><a class= 'ex4'>יֵ</a></div></td><td><div class='font'><a class= 'ex4'>יֶ</a></div></td><td><div class='font'><a class= 'ex4'>יַ</a></div></td><td><div class='font'><a class= 'ex4'>יָ</a></div></td><td><div class='font'><a class= 'ex4'>יׇ</a></div></td><td><div class='font'><a class= 'ex4'>יֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>יֻ</a></div></td><td><div class='font'><a class= 'ex4'>יּ</a></div></td><td><div class='font'><a class= 'ex4'>יֽ</a></div></td><td><div class='font'><a class= 'ex4'>יֿ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>יִ</a></div></td><td><div class='font'><a class= 'ex4'>ײַ</a></div></td><td><div class='font'><a class= 'ex4'>ײ</a></div></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>י֯</a></div></td><td><div class='font'><a class= 'ex4'>יׄ</a></div></td><td><div class='font'><a class= 'ex4'>יׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>כ</a></div></td><td><div class='font'><a class= 'ex4'>כְ</a></div></td><td><div class='font'><a class= 'ex4'>כֱ</a></div></td><td><div class='font'><a class= 'ex4'>כֲ</a></div></td><td><div class='font'><a class= 'ex4'>כֳ</a></div></td><td><div class='font'><a class= 'ex4'>כִ</a></div></td><td><div class='font'><a class= 'ex4'>כֵ</a></div></td><td><div class='font'><a class= 'ex4'>כֶ</a></div></td><td><div class='font'><a class= 'ex4'>כַ</a></div></td><td><div class='font'><a class= 'ex4'>כָ</a></div></td><td><div class='font'><a class= 'ex4'>כׇּ</a></div></td><td><div class='font'><a class= 'ex4'>כֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>כֻ</a></div></td><td><div class='font'><a class= 'ex4'>כּ</a></div></td><td><div class='font'><a class= 'ex4'>כֽ</a></div></td><td><div class='font'><a class= 'ex4'>כֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬤ</a></div></td><td><div class='font'><a class= 'ex4'>כ֯</a></div></td><td><div class='font'><a class= 'ex4'>כׄ</a></div></td><td><div class='font'><a class= 'ex4'>כׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ך</a></div></td><td><div class='font'><a class= 'ex4'>ךְ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ךָ</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ךּ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ך֯</a></div></td><td><div class='font'><a class= 'ex4'>ךׄ</a></div></td><td><div class='font'><a class= 'ex4'>ךׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ל</a></div></td><td><div class='font'><a class= 'ex4'>לְ</a></div></td><td><div class='font'><a class= 'ex4'>לֱ</a></div></td><td><div class='font'><a class= 'ex4'>לֲ</a></div></td><td><div class='font'><a class= 'ex4'>לֳ</a></div></td><td><div class='font'><a class= 'ex4'>לִ</a></div></td><td><div class='font'><a class= 'ex4'>לֵ</a></div></td><td><div class='font'><a class= 'ex4'>לֶ</a></div></td><td><div class='font'><a class= 'ex4'>לַ</a></div></td><td><div class='font'><a class= 'ex4'>לָ</a></div></td><td><div class='font'><a class= 'ex4'>לׇ</a></div></td><td><div class='font'><a class= 'ex4'>לֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>לֻ</a></div></td><td><div class='font'><a class= 'ex4'>לּ</a></div></td><td><div class='font'><a class= 'ex4'>לֽ</a></div></td><td><div class='font'><a class= 'ex4'>לֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬥ</a></div></td><td><div class='font'><a class= 'ex4'>ל֯</a></div></td><td><div class='font'><a class= 'ex4'>לׄ</a></div></td><td><div class='font'><a class= 'ex4'>לׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>מ</a></div></td><td><div class='font'><a class= 'ex4'>מְ</a></div></td><td><div class='font'><a class= 'ex4'>מֱ</a></div></td><td><div class='font'><a class= 'ex4'>מֲ</a></div></td><td><div class='font'><a class= 'ex4'>מֳ</a></div></td><td><div class='font'><a class= 'ex4'>מִ</a></div></td><td><div class='font'><a class= 'ex4'>מֵ</a></div></td><td><div class='font'><a class= 'ex4'>מֶ</a></div></td><td><div class='font'><a class= 'ex4'>מַ</a></div></td><td><div class='font'><a class= 'ex4'>מָ</a></div></td><td><div class='font'><a class= 'ex4'>מׇ</a></div></td><td><div class='font'><a class= 'ex4'>מֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>מֻ</a></div></td><td><div class='font'><a class= 'ex4'>מּ</a></div></td><td><div class='font'><a class= 'ex4'>מֽ</a></div></td><td><div class='font'><a class= 'ex4'>מֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>מ֯</a></div></td><td><div class='font'><a class= 'ex4'>מׄ</a></div></td><td><div class='font'><a class= 'ex4'>מׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ם</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>םִ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬦ</a></div></td><td><div class='font'><a class= 'ex4'>ם֯</a></div></td><td><div class='font'><a class= 'ex4'>םׄ</a></div></td><td><div class='font'><a class= 'ex4'>םׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>נ</a></div></td><td><div class='font'><a class= 'ex4'>נְ</a></div></td><td><div class='font'><a class= 'ex4'>נֱ</a></div></td><td><div class='font'><a class= 'ex4'>נֲ</a></div></td><td><div class='font'><a class= 'ex4'>נֳ</a></div></td><td><div class='font'><a class= 'ex4'>נִ</a></div></td><td><div class='font'><a class= 'ex4'>נֵ</a></div></td><td><div class='font'><a class= 'ex4'>נֶ</a></div></td><td><div class='font'><a class= 'ex4'>נַ</a></div></td><td><div class='font'><a class= 'ex4'>נָ</a></div></td><td><div class='font'><a class= 'ex4'>נׇ</a></div></td><td><div class='font'><a class= 'ex4'>נֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>נֻ</a></div></td><td><div class='font'><a class= 'ex4'>נּ</a></div></td><td><div class='font'><a class= 'ex4'>נֽ</a></div></td><td><div class='font'><a class= 'ex4'>נֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>׆</a></div></td><td></td><td><div class='font'><a class= 'ex4'>נ֯</a></div></td><td><div class='font'><a class= 'ex4'>נׄ</a></div></td><td><div class='font'><a class= 'ex4'>נׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ן</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ןָ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ן֯</a></div></td><td><div class='font'><a class= 'ex4'>ןׄ</a></div></td><td><div class='font'><a class= 'ex4'>ןׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ס</a></div></td><td><div class='font'><a class= 'ex4'>סְ</a></div></td><td><div class='font'><a class= 'ex4'>סֱ</a></div></td><td><div class='font'><a class= 'ex4'>סֲ</a></div></td><td><div class='font'><a class= 'ex4'>סֳ</a></div></td><td><div class='font'><a class= 'ex4'>סִ</a></div></td><td><div class='font'><a class= 'ex4'>סֵ</a></div></td><td><div class='font'><a class= 'ex4'>סֶ</a></div></td><td><div class='font'><a class= 'ex4'>סַ</a></div></td><td><div class='font'><a class= 'ex4'>סָ</a></div></td><td><div class='font'><a class= 'ex4'>סׇ</a></div></td><td><div class='font'><a class= 'ex4'>סֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>סֻ</a></div></td><td><div class='font'><a class= 'ex4'>סּ</a></div></td><td><div class='font'><a class= 'ex4'>סֽ</a></div></td><td><div class='font'><a class= 'ex4'>סֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ס֯</a></div></td><td><div class='font'><a class= 'ex4'>סׄ</a></div></td><td><div class='font'><a class= 'ex4'>סׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ע</a></div></td><td><div class='font'><a class= 'ex4'>עְ</a></div></td><td><div class='font'><a class= 'ex4'>עֱ</a></div></td><td><div class='font'><a class= 'ex4'>עֲ</a></div></td><td><div class='font'><a class= 'ex4'>עֳ</a></div></td><td><div class='font'><a class= 'ex4'>עִ</a></div></td><td><div class='font'><a class= 'ex4'>עֵ</a></div></td><td><div class='font'><a class= 'ex4'>עֶ</a></div></td><td><div class='font'><a class= 'ex4'>עַ</a></div></td><td><div class='font'><a class= 'ex4'>עָ</a></div></td><td><div class='font'><a class= 'ex4'>עׇ</a></div></td><td><div class='font'><a class= 'ex4'>עֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>עֻ</a></div></td><td><div class='font'><a class= 'ex4'>עּ</a></div></td><td><div class='font'><a class= 'ex4'>עֽ</a></div></td><td><div class='font'><a class= 'ex4'>עֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬠ</a></div></td><td><div class='font'><a class= 'ex4'>ע֯</a></div></td><td><div class='font'><a class= 'ex4'>עׄ</a></div></td><td><div class='font'><a class= 'ex4'>עׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>פ</a></div></td><td><div class='font'><a class= 'ex4'>פְ</a></div></td><td><div class='font'><a class= 'ex4'>פֱ</a></div></td><td><div class='font'><a class= 'ex4'>פֲ</a></div></td><td><div class='font'><a class= 'ex4'>פֳ</a></div></td><td><div class='font'><a class= 'ex4'>פִ</a></div></td><td><div class='font'><a class= 'ex4'>פֵ</a></div></td><td><div class='font'><a class= 'ex4'>פֶ</a></div></td><td><div class='font'><a class= 'ex4'>פַ</a></div></td><td><div class='font'><a class= 'ex4'>פָ</a></div></td><td><div class='font'><a class= 'ex4'>פׇ</a></div></td><td><div class='font'><a class= 'ex4'>פֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>פֻ</a></div></td><td><div class='font'><a class= 'ex4'>פּ</a></div></td><td><div class='font'><a class= 'ex4'>פֽ</a></div></td><td><div class='font'><a class= 'ex4'>פֿ</a></div></td><td><div class='font'><a class= 'ex4'>פﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>פ֯</a></div></td><td><div class='font'><a class= 'ex4'>פׄ</a></div></td><td><div class='font'><a class= 'ex4'>פׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ף</a></div></td><td><div class='font'><a class= 'ex4'>ףְ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ףּ</a></div></td><td></td><td></td><td><div class='font'><a class= 'ex4'>  </a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ף֯</a></div></td><td><div class='font'><a class= 'ex4'>ףׄ </a></div></td><td><div class='font'><a class= 'ex4'>ףׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>צ</a></div></td><td><div class='font'><a class= 'ex4'>צְ</a></div></td><td><div class='font'><a class= 'ex4'>צֱ</a></div></td><td><div class='font'><a class= 'ex4'>צֲ</a></div></td><td><div class='font'><a class= 'ex4'>צֳ</a></div></td><td><div class='font'><a class= 'ex4'>צִ</a></div></td><td><div class='font'><a class= 'ex4'>צֵ</a></div></td><td><div class='font'><a class= 'ex4'>צֶ</a></div></td><td><div class='font'><a class= 'ex4'>צַ</a></div></td><td><div class='font'><a class= 'ex4'>צָ</a></div></td><td><div class='font'><a class= 'ex4'>צׇ</a></div></td><td><div class='font'><a class= 'ex4'>צֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>צֻ</a></div></td><td><div class='font'><a class= 'ex4'>צּ</a></div></td><td><div class='font'><a class= 'ex4'>צֽ</a></div></td><td><div class='font'><a class= 'ex4'>צֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>צ֯</a></div></td><td><div class='font'><a class= 'ex4'>צׄ</a></div></td><td><div class='font'><a class= 'ex4'>צׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ץ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ץ֯</a></div></td><td><div class='font'><a class= 'ex4'>ץׄ</a></div></td><td><div class='font'><a class= 'ex4'>ץׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ק</a></div></td><td><div class='font'><a class= 'ex4'>קְ</a></div></td><td><div class='font'><a class= 'ex4'>קֱ</a></div></td><td><div class='font'><a class= 'ex4'>קֲ</a></div></td><td><div class='font'><a class= 'ex4'>קֳ</a></div></td><td><div class='font'><a class= 'ex4'>קִ</a></div></td><td><div class='font'><a class= 'ex4'>קֵ</a></div></td><td><div class='font'><a class= 'ex4'>קֶ</a></div></td><td><div class='font'><a class= 'ex4'>קַ</a></div></td><td><div class='font'><a class= 'ex4'>קָ</a></div></td><td><div class='font'><a class= 'ex4'>קׇ</a></div></td><td><div class='font'><a class= 'ex4'>קֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>קֻ</a></div></td><td><div class='font'><a class= 'ex4'>קּ</a></div></td><td><div class='font'><a class= 'ex4'>קֽ</a></div></td><td><div class='font'><a class= 'ex4'>קֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ק֯</a></div></td><td><div class='font'><a class= 'ex4'>קׄ</a></div></td><td><div class='font'><a class= 'ex4'>קׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ר</a></div></td><td><div class='font'><a class= 'ex4'>רְ</a></div></td><td><div class='font'><a class= 'ex4'>רֱ</a></div></td><td><div class='font'><a class= 'ex4'>רֲ</a></div></td><td><div class='font'><a class= 'ex4'>רֳ</a></div></td><td><div class='font'><a class= 'ex4'>רִ</a></div></td><td><div class='font'><a class= 'ex4'>רֵ</a></div></td><td><div class='font'><a class= 'ex4'>רֶ</a></div></td><td><div class='font'><a class= 'ex4'>רַ</a></div></td><td><div class='font'><a class= 'ex4'>רָ</a></div></td><td><div class='font'><a class= 'ex4'>רׇ</a></div></td><td><div class='font'><a class= 'ex4'>רֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>רֻ</a></div></td><td><div class='font'><a class= 'ex4'>רּ</a></div></td><td><div class='font'><a class= 'ex4'>רֽ</a></div></td><td><div class='font'><a class= 'ex4'>רֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬧ</a></div></td><td><div class='font'><a class= 'ex4'>ר֯</a></div></td><td><div class='font'><a class= 'ex4'>רׄ</a></div></td><td><div class='font'><a class= 'ex4'>רׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>שׁ</a></div></td><td><div class='font'><a class= 'ex4'>שְׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֱׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֲׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֳׁ</a></div></td><td><div class='font'><a class= 'ex4'>שִׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֵׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֶׁ</a></div></td><td><div class='font'><a class= 'ex4'>שַׁ</a></div></td><td><div class='font'><a class= 'ex4'>שָׁ</a></div></td><td><div class='font'><a class= 'ex4'>שׇׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֹׁ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>שֻׁ</a></div></td><td><div class='font'><a class= 'ex4'>שּׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֽׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֿׁ</a></div></td><td><div class='font'><a class= 'ex4'>שﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>שׁ֯</a></div></td><td><div class='font'><a class= 'ex4'>שׁׄ</a></div></td><td><div class='font'><a class= 'ex4'>שׁׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>שׂ</a></div></td><td><div class='font'><a class= 'ex4'>שְׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֱׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֲׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֳׂ</a></div></td><td><div class='font'><a class= 'ex4'>שִׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֵׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֶׂ</a></div></td><td><div class='font'><a class= 'ex4'>שַׂ</a></div></td><td><div class='font'><a class= 'ex4'>שָׂ</a></div></td><td><div class='font'><a class= 'ex4'>שׇׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֹׂ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>שֻׂ</a></div></td><td><div class='font'><a class= 'ex4'>שּׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֽׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֿׂ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>שׂ֯</a></div></td><td><div class='font'><a class= 'ex4'>שׂׄ</a></div></td><td><div class='font'><a class= 'ex4'>שׂׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ת</a></div></td><td><div class='font'><a class= 'ex4'>תְ</a></div></td><td><div class='font'><a class= 'ex4'>תֱ</a></div></td><td><div class='font'><a class= 'ex4'>תֲ</a></div></td><td><div class='font'><a class= 'ex4'>תֳ</a></div></td><td><div class='font'><a class= 'ex4'>תִ</a></div></td><td><div class='font'><a class= 'ex4'>תֵ</a></div></td><td><div class='font'><a class= 'ex4'>תֶ</a></div></td><td><div class='font'><a class= 'ex4'>תַ</a></div></td><td><div class='font'><a class= 'ex4'>תָ</a></div></td><td><div class='font'><a class= 'ex4'>תׇ</a></div></td><td><div class='font'><a class= 'ex4'>תֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>תֻ</a></div></td><td><div class='font'><a class= 'ex4'>תּ</a></div></td><td><div class='font'><a class= 'ex4'>תֽ</a></div></td><td><div class='font'><a class= 'ex4'>תֿ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬨ</a></div></td><td><div class='font'><a class= 'ex4'>ת֯</a></div></td><td><div class='font'><a class= 'ex4'>תׄ</a></div></td><td><div class='font'><a class= 'ex4'>תׅ </a></div></td></tr>
 </table>
 <hr />
 <h3>T'amim (Cantillation Marks)</h3>
  <div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover for reference implementation in SBL Hebrew.</div>
 <table><tr><td><div class='font'>
 <a class='ex4'>קַדְמָ֨א</a> <a class='ex4'>מֻנַּ֣ח</a> <a class='ex4'>זַרְקָא֮</a> <a class='ex4'>מֻנַּ֣ח</a> <a class='ex4'>סֶגּוֹל֒</a> <a class='ex4'>מֻנַּ֣ח</a> <a class='ex4'>׀</a> <a class='ex4'>מֻנַּ֣ח</a> <a class='ex4'>רְבִ֗יע</a> <a class='ex4'>מַהְפַּ֤ך</a> <a class='ex4'>פַּשְׁטָא֙</a> <a class='ex4'>זָקֵף־קָטָ֔ן</a> <a class='ex4'>זָקֵף־גָּד֕וֹל</a> <a class='ex4'>מֵרְכָ֥א</a> <a class='ex4'>טִפְּחָ֖א</a> <a class='ex4'>מֻנַּ֣ח</a> <a class='ex4'>אֶתְנַחְתָּ֑א</a> <a class='ex4'>פָּזֵ֡ר</a> <a class='ex4'>תְּלִישָא־קְטַנָּה֩</a> <a class='ex4'>תְּ֠לִישָא</a> <a class='ex4'>גְדוֹלָה</a> <a class='ex4'>קַדְמָ֨א</a> <a class='ex4'>וְאַזְלָ֜א</a> <a class='ex4'>אַזְלָא־גֵּ֜רֵשׁ</a> <a class='ex4'>גֵּרְשַׁ֞יִם</a> <a class='ex4'>דַּרְגָּ֧א</a> <a class='ex4'>תְּבִ֛יר</a> <a class='ex4'>יְ֚תִיב</a> <a class='ex4'>פְּסִיק׀</a> <a class='ex4'>מֵרְכָ֥א</a> <a class='ex4'>טִפְּחָ֖א</a> <a class='ex4'>מֵרְכָ֥א</a> <a class='ex4'>סוֹף</a> <a class='ex4'>פָּסֽוּק׃</a> <a class='ex4'>שַׁלְשֶׁ֓לֶת</a> <a class='ex4'>מֵרְכָא</a> <a class='ex4'>כְּפוּלָ֦ה</a> <a class='ex4'>יֵרֶח</a> <a class='ex4'>בֶּן</a> <a class='ex4'>יוֹמ֪וֹ</a> <a class='ex4'>קַרְנֵי</a> <a class='ex4'>פָרָ֟ה׃</a> 
</div></td></tr>
 </table>
 <p /><br />
 
 <hr />
 
<h3>Parentheses and Brackets</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover for reference implementation in SBL Hebrew.</div>
<table><tr><td><div class='font' style='text-align: center; direction: rtl;'>
<a class='ex4'>(”שָׁלוֹם עוֹלַם“)</a> <a>“Shalom Olam”</a> <a class='ex4'><שָׁלוֹם עוֹלַם?></a>  <a class='ex4'>{שָׁלוֹם עוֹלַם}</a>  <a class='ex4'>[שָׁלוֹם עוֹלַם!]</a> 
</div></td></tr>
 </table> 
 <p /><br />
 
 <hr />
 
 <h3>Punctuation, Numbers, and Symbols</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover any missing glyphs in $fnt to display the Unicode character in FreeSerif.</div>
<table><tr><td><div class='font' style='text-align: center; direction: ltr;'>
<a>#</a> <a>№</a> <a>∅</a> <a>0</a> <a>1</a> <a>2</a> <a>3</a> <a>4</a> <a>5</a> <a>6</a> <a>7</a> <a>8</a> <a>9</a> <a>%</a> <a>‰</a> <a>‱</a> <a>¼</a> <a>½</a> <a>¾</a> <a>∞</a>
<a>₪</a> <a>€</a> <a>£</a> <a>$</a> <a>¢</a> <a>¥</a>
<a>±</a> <a>+</a> <a>﬩</a> <a>−</a> <a>×</a> <a>÷</a> <a>≈</a> <a>=</a> <a>≡</a> <a>≠</a> <a>∑</a>
<a>§</a> <a>¶</a> <a>◌</a> <a>‽</a> <a>❜</a> <a>‸</a> <a>¿</a> <a>¡</a> <a>\"</a> <a>❛</a> <a>@</a> 
<a>©</a> <a>^</a> <a>&</a> <a>*</a> <a>\</a> <a>/</a> <a>~</a> <a>`</a> <a>,</a> <a>;</a> <a>:</a> <a>.</a> <a>●</a> <a>•</a>
<a>ℸ</a> <a>ℷ</a> <a>ℶ</a> <a>ℵ</a>
<a>☜</a> <a>☞</a> <a>☝</a> <a>☟</a> <a>←</a> <a>↕</a> <a>→</a> <a>↑</a> <a>↔</a> <a>✍</a> <a>℅</a> <a>✓</a>
<a>†</a> <a>‡</a> <a>▪</a> <a>□</a> <a>∇</a> <a>◊</a> <a>⁂</a> <a>⌘</a> <a>★</a> <a>☆</a>
<a>☁</a> <a>°</a> <a>℃</a> <a>℉</a> <a>☂</a> <a>⛈</a> <a>☔</a> <a>☄</a> <a>☉</a> <a>✴</a> <a>✶</a>
<a>♡</a> <a>♛</a> <a>♚</a> <a>♥</a> <a>❥</a> <a>❧</a> <a>❦</a> <a>☠</a>
<a>☾</a> <a>☿</a> <a>♀</a> <a>♁</a> <a>♂</a> <a>♃</a> <a>♄</a> <a>☽</a> <a>♍</a> <a>♒</a>
<a>☢</a> <a>☣</a> <a>✂</a> <a>░</a> <a>▒</a> <a>▓</a> <a>♪</a> <a>♫</a> <a>☺</a> <a>☹</a>
<a>☤</a> <a>⚕</a> <a>☥</a> <a>✡</a> <a>☩</a> <a>☪</a> <a>☬</a> <a>🕍</a> 
</div></td></tr>
 </table>
 <hr />
<h3>Basic Latin</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover any missing glyphs in $fnt to display the Unicode character in FreeSerif.</div>
<table><tr><td><div class='font' style='text-align: center; '>
<a>A</a> <a>B</a> <a>C</a> <a>D</a> <a>E</a> <a>F</a> <a>G</a> <a>H</a> <a>I</a> <a>J</a> <a>K</a> <a>L</a> <a>M</a> <a>N</a> <a>O</a> <a>P</a> <a>Q</a> <a>R</a> <a>S</a> <a>T</a> <a>U</a> <a>V</a> <a>W</a> <a>X</a> <a>Y</a> <a>Z</a><br />
<a>a</a> <a>b</a> <a>c</a> <a>d</a> <a>e</a> <a>f</a> <a>g</a> <a>h</a> <a>i</a> <a>j</a> <a>k</a> <a>l</a> <a>m</a> <a>n</a> <a>o</a> <a>p</a> <a>q</a> <a>r</a> <a>s</a> <a>t</a> <a>u</a> <a>v</a> <a>w</a> <a>x</a> <a>y</a> <a>z</a> 
</div></td></tr>
</table>
 
<h3>Latin-1 Supplement</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover any missing glyphs in $fnt to display the Unicode character in FreeSerif.</div>
<table><tr><td><div class='font' style='text-align: center; '>
<a>À</a> <a>Á</a> <a>Â</a> <a>Ã</a> <a>Ä</a> <a>Å</a> <a>Æ</a> <a>Ç</a> <a>È</a> <a>É</a> <a>Ê</a> <a>Ë</a> <a>Ì</a> <a>Í</a> <a>Î</a> <a>Ï</a> <a>Ð</a> <a>Ñ</a> <a>Ò</a> <a>Ó</a> <a>Ô</a> <a>Õ</a> <a>Ö</a> <a>Ø</a> <a>Ù</a> <a>Ú</a> <a>Û</a> <a>Ü</a> <a>Ý</a> <a>Þ</a> <a>ß</a><br />
<a>à</a> <a>á</a> <a>â</a> <a>ã</a> <a>ä</a> <a>å</a> <a>æ</a> <a>ç</a> <a>è</a> <a>é</a> <a>ê</a> <a>ë</a> <a>ì</a> <a>í</a> <a>î</a> <a>ï</a> <a>ð</a> <a>ñ</a> <a>ò</a> <a>ó</a> <a>ô</a> <a>õ</a> <a>ö</a> <a>ø</a> <a>ù</a> <a>ú</a> <a>û</a> <a>ü</a> <a>ý</a> <a>þ</a> <a>ÿ</a>
</div></td></tr>
</table>
 
<h3>Latin Extended-A</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover any missing glyphs in $fnt to display the Unicode character in FreeSerif.</div>
<table><tr><td><div class='font' style='text-align: left; '>
<a>Ā</a> <a>ā</a> <a>Ă</a> <a>ă</a> <a>Ą</a> <a>ą</a> <a>Ć</a> <a>ć</a> <a>Ĉ</a>
<a>ĉ</a> <a>Ċ</a> <a>ċ</a> <a>Č</a> <a>č</a> <a>Ď</a> <a>ď</a> <a>Đ</a> <a>đ</a>
<a>Ē</a> <a>ē</a> <a>Ĕ</a> <a>ĕ</a> <a>Ė</a> <a>ė</a> <a>Ę</a> <a>ę</a> <a>Ě</a>
<a>ě</a> <a>Ĝ</a> <a>ĝ</a> <a>Ğ</a> <a>ğ</a> <a>Ġ</a> <a>ġ</a> <a>Ģ</a> <a>ģ</a>
<a>Ĥ</a> <a>ĥ</a> <a>Ħ</a> <a>ħ</a> <a>Ĩ</a> <a>ĩ</a> <a>Ī</a> <a>ī</a> <a>Ĭ</a>
<a>ĭ</a> <a>Į</a> <a>į</a> <a>İ</a> <a>ı</a> <a>Ĳ</a> <a>ĳ</a> <a>Ĵ</a> <a>ĵ</a>
<a>Ķ</a> <a>ķ</a> <a>ĸ</a> <a>Ĺ</a> <a>ĺ</a> <a>Ļ</a> <a>ļ</a> <a>Ľ</a> <a>ľ</a>
<a>Ŀ</a> <a>ŀ</a> <a>Ł</a> <a>ł</a> <a>Ń</a> <a>ń</a> <a>Ņ</a> <a>ņ</a> <a>Ň</a>
<a>ň</a> <a>ŉ</a> <a>Ŋ</a> <a>ŋ</a> <a>Ō</a> <a>ō</a> <a>Ŏ</a> <a>ŏ</a> <a>Ő</a>
<a>ő</a> <a>Œ</a> <a>œ</a> <a>Ŕ</a> <a>ŕ</a> <a>Ŗ</a> <a>ŗ</a> <a>Ř</a> <a>
</a> <a>Ś</a> <a>ś</a> <a>Ŝ</a> <a>ŝ</a> <a>Ş</a> <a>ş</a> <a>Š</a> <a>š</a>
<a>Ţ</a> <a>ţ</a> <a>Ť</a> <a>ť</a> <a>Ŧ</a> <a>ŧ</a> <a>Ũ</a> <a>ũ</a> <a>Ū</a>
<a>ū</a> <a>Ŭ</a> <a>ŭ</a> <a>Ů</a> <a>ů</a> <a>Ű</a> <a>ű</a> <a>Ų</a> <a>ų</a>
<a>Ŵ</a> <a>ŵ</a> <a>Ŷ</a> <a>ŷ</a> <a>Ÿ</a> <a>Ź</a> <a>ź</a> <a>Ż</a> <a>ż</a>
<a>Ž</a> <a>ž</a> <a>ſ</a>
</div></td></tr>
</table>
 
<h3>Latin Extended-B</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover any missing glyphs in $fnt to display the Unicode character in FreeSerif.</div>
<table><tr><td><div class='font' style='text-align: left; '>
<a>ƀ</a> <a>Ɓ</a> <a>Ƃ</a> <a>ƃ</a> <a>Ƅ</a> <a>ƅ</a> <a>Ɔ</a> <a>Ƈ</a> <a>ƈ</a>
<a>Ɖ</a> <a>Ɗ</a> <a>Ƌ</a> <a>ƌ</a> <a>ƍ</a> <a>Ǝ</a> <a>Ə</a> <a>Ɛ</a> <a>Ƒ</a>
<a>ƒ</a> <a>Ɠ</a> <a>Ɣ</a> <a>ƕ</a> <a>Ɩ</a> <a>Ɨ</a> <a>Ƙ</a> <a>ƙ</a> <a>ƚ</a>
<a>ƛ</a> <a>Ɯ</a> <a>Ɲ</a> <a>ƞ</a> <a>Ɵ</a> <a>Ơ</a> <a>ơ</a> <a>Ƣ</a> <a>ƣ</a>
<a>Ƥ</a> <a>ƥ</a> <a>Ʀ</a> <a>Ƨ</a> <a>ƨ</a> <a>Ʃ</a> <a>ƪ</a> <a>ƫ</a> <a>Ƭ</a>
<a>ƭ</a> <a>Ʈ</a> <a>Ư</a> <a>ư</a> <a>Ʊ</a> <a>Ʋ</a> <a>Ƴ</a> <a>ƴ</a> <a>Ƶ</a>
<a>ƶ</a> <a>Ʒ</a> <a>Ƹ</a> <a>ƹ</a> <a>ƺ</a> <a>ƻ</a> <a>Ƽ</a> <a>ƽ</a> <a>ƾ</a>
<a>ƿ</a> <a>ǀ</a> <a>ǁ</a> <a>ǂ</a> <a>ǃ</a> <a>Ǆ</a> <a>ǅ</a> <a>ǆ</a> <a>Ǉ</a>
<a>ǈ</a> <a>ǉ</a> <a>Ǌ</a> <a>ǋ</a> <a>ǌ</a> <a>Ǎ</a> <a>ǎ</a> <a>Ǐ</a> <a>ǐ</a>
<a>Ǒ</a> <a>ǒ</a> <a>Ǔ</a> <a>ǔ</a> <a>Ǖ</a> <a>ǖ</a> <a>Ǘ</a> <a>ǘ</a> <a>Ǚ</a>
<a>ǚ</a> <a>Ǜ</a> <a>ǜ</a> <a>ǝ</a> <a>Ǟ</a> <a>ǟ</a> <a>Ǡ</a> <a>ǡ</a> <a>Ǣ</a>
<a>ǣ</a> <a>Ǥ</a> <a>ǥ</a> <a>Ǧ</a> <a>ǧ</a> <a>Ǩ</a> <a>ǩ</a> <a>Ǫ</a> <a>ǫ</a>
<a>Ǭ</a> <a>ǭ</a> <a>Ǯ</a> <a>ǯ</a> <a>ǰ</a> <a>Ǳ</a> <a>ǲ</a> <a>ǳ</a> <a>Ǵ</a>
<a>ǵ</a> <a>Ƕ</a> <a>Ƿ</a> <a>Ǹ</a> <a>ǹ</a> <a>Ǻ</a> <a>ǻ</a> <a>Ǽ</a> <a>ǽ</a>
<a>Ǿ</a> <a>ǿ</a> <a>Ȁ</a> <a>ȁ</a> <a>Ȃ</a> <a>ȃ</a> <a>Ȅ</a> <a>ȅ</a> <a>Ȇ</a>
<a>ȇ</a> <a>Ȉ</a> <a>ȉ</a> <a>Ȋ</a> <a>ȋ</a> <a>Ȍ</a> <a>ȍ</a> <a>Ȏ</a> <a>ȏ</a>
<a>Ȑ</a> <a>ȑ</a> <a>Ȓ</a> <a>ȓ</a> <a>Ȕ</a> <a>ȕ</a> <a>Ȗ</a> <a>ȗ</a> <a>Ș</a>
<a>ș</a> <a>Ț</a> <a>ț</a> <a>Ȝ</a> <a>ȝ</a> <a>Ȟ</a> <a>ȟ</a> <a>Ƞ</a> <a>ȡ</a>
<a>Ȣ</a> <a>ȣ</a> <a>Ȥ</a> <a>ȥ</a> <a>Ȧ</a> <a>ȧ</a> <a>Ȩ</a> <a>ȩ</a> <a>Ȫ</a>
<a>ȫ</a> <a>Ȭ</a> <a>ȭ</a> <a>Ȯ</a> <a>ȯ</a> <a>Ȱ</a> <a>ȱ</a> <a>Ȳ</a> <a>ȳ</a>
<a>ȴ</a> <a>ȵ</a> <a>ȶ</a> <a>ȷ</a> <a>ȸ</a> <a>ȹ</a> <a>Ⱥ</a> <a>Ȼ</a> <a>ȼ</a>
<a>Ƚ</a> <a>Ⱦ</a> <a>ȿ</a> <a>ɀ</a> <a>Ɂ</a> <a>ɂ</a> <a>Ƀ</a> <a>Ʉ</a> <a>Ʌ</a>
<a>Ɇ</a> <a>ɇ</a> <a>Ɉ</a> <a>ɉ</a> <a>Ɋ</a> <a>ɋ</a> <a>Ɍ</a> <a>ɍ</a> <a>Ɏ</a> <a>ɏ</a> 
</div></td></tr>
</table>

<h3>Latin Extended Additional</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover any missing glyphs in $fnt to display the Unicode character in FreeSerif.</div>
<table><tr><td><div class='font' style='text-align: left; '>
<a>Ḁ</a> <a>ḁ</a> <a>Ḃ</a> <a>ḃ</a> <a>Ḅ</a> <a>ḅ</a> <a>Ḇ</a> <a>ḇ</a> <a>Ḉ</a>
<a>ḉ</a> <a>Ḋ</a> <a>ḋ</a> <a>Ḍ</a> <a>ḍ</a> <a>Ḏ</a> <a>ḏ</a> <a>Ḑ</a> <a>ḑ</a>
<a>Ḓ</a> <a>ḓ</a> <a>Ḕ</a> <a>ḕ</a> <a>Ḗ</a> <a>ḗ</a> <a>Ḙ</a> <a>ḙ</a> <a>Ḛ</a>
<a>ḛ</a> <a>Ḝ</a> <a>ḝ</a> <a>Ḟ</a> <a>ḟ</a> <a>Ḡ</a> <a>ḡ</a> <a>Ḣ</a> <a>ḣ</a>
<a>Ḥ</a> <a>ḥ</a> <a>Ḧ</a> <a>ḧ</a> <a>Ḩ</a> <a>ḩ</a> <a>Ḫ</a> <a>ḫ</a> <a>Ḭ</a>
<a>ḭ</a> <a>Ḯ</a> <a>ḯ</a> <a>Ḱ</a> <a>ḱ</a> <a>Ḳ</a> <a>ḳ</a> <a>Ḵ</a> <a>ḵ</a>
<a>Ḷ</a> <a>ḷ</a> <a>Ḹ</a> <a>ḹ</a> <a>Ḻ</a> <a>ḻ</a> <a>Ḽ</a> <a>ḽ</a> <a>Ḿ</a>
<a>ḿ</a> <a>Ṁ</a> <a>ṁ</a> <a>Ṃ</a> <a>ṃ</a> <a>Ṅ</a> <a>ṅ</a> <a>Ṇ</a> <a>ṇ</a>
<a>Ṉ</a> <a>ṉ</a> <a>Ṋ</a> <a>ṋ</a> <a>Ṍ</a> <a>ṍ</a> <a>Ṏ</a> <a>ṏ</a> <a>Ṑ</a>
<a>ṑ</a> <a>Ṓ</a> <a>ṓ</a> <a>Ṕ</a> <a>ṕ</a> <a>Ṗ</a> <a>ṗ</a> <a>Ṙ</a> <a>ṙ</a>
<a>Ṛ</a> <a>ṛ</a> <a>Ṝ</a> <a>ṝ</a> <a>Ṟ</a> <a>ṟ</a> <a>Ṡ</a> <a>ṡ</a> <a>Ṣ</a>
<a>ṣ</a> <a>Ṥ</a> <a>ṥ</a> <a>Ṧ</a> <a>ṧ</a> <a>Ṩ</a> <a>ṩ</a> <a>Ṫ</a> <a>ṫ</a>
<a>Ṭ</a> <a>ṭ</a> <a>Ṯ</a> <a>ṯ</a> <a>Ṱ</a> <a>ṱ</a> <a>Ṳ</a> <a>ṳ</a> <a>Ṵ</a>
<a>ṵ</a> <a>Ṷ</a> <a>ṷ</a> <a>Ṹ</a> <a>ṹ</a> <a>Ṻ</a> <a>ṻ</a> <a>Ṽ</a> <a>ṽ</a>
<a>Ṿ</a> <a>ṿ</a> <a>Ẁ</a> <a>ẁ</a> <a>Ẃ</a> <a>ẃ</a> <a>Ẅ</a> <a>ẅ</a> <a>Ẇ</a>
<a>ẇ</a> <a>Ẉ</a> <a>ẉ</a> <a>Ẋ</a> <a>ẋ</a> <a>Ẍ</a> <a>ẍ</a> <a>Ẏ</a> <a>ẏ</a>
<a>Ẑ</a> <a>ẑ</a> <a>Ẓ</a> <a>ẓ</a> <a>Ẕ</a> <a>ẕ</a> <a>ẖ</a> <a>ẗ</a> <a>ẘ</a>
<a>ẙ</a> <a>ẚ</a> <a>ẛ</a> <a>ẜ</a> <a>ẝ</a> <a>ẞ</a> <a>ẟ</a> <a>Ạ</a> <a>ạ</a>
<a>Ả</a> <a>ả</a> <a>Ấ</a> <a>ấ</a> <a>Ầ</a> <a>ầ</a> <a>Ẩ</a> <a>ẩ</a> <a>Ẫ</a>
<a>ẫ</a> <a>Ậ</a> <a>ậ</a> <a>Ắ</a> <a>ắ</a> <a>Ằ</a> <a>ằ</a> <a>Ẳ</a> <a>ẳ</a>
<a>Ẵ</a> <a>ẵ</a> <a>Ặ</a> <a>ặ</a> <a>Ẹ</a> <a>ẹ</a> <a>Ẻ</a> <a>ẻ</a> <a>Ẽ</a>
<a>ẽ</a> <a>Ế</a> <a>ế</a> <a>Ề</a> <a>ề</a> <a>Ể</a> <a>ể</a> <a>Ễ</a> <a>ễ</a>
<a>Ệ</a> <a>ệ</a> <a>Ỉ</a> <a>ỉ</a> <a>Ị</a> <a>ị</a> <a>Ọ</a> <a>ọ</a> <a>Ỏ</a>
<a>ỏ</a> <a>Ố</a> <a>ố</a> <a>Ồ</a> <a>ồ</a> <a>Ổ</a> <a>ổ</a> <a>Ỗ</a> <a>ỗ</a>
<a>Ộ</a> <a>ộ</a> <a>Ớ</a> <a>ớ</a> <a>Ờ</a> <a>ờ</a> <a>Ở</a> <a>ở</a> <a>Ỡ</a>
<a>ỡ</a> <a>Ợ</a> <a>ợ</a> <a>Ụ</a> <a>ụ</a> <a>Ủ</a> <a>ủ</a> <a>Ứ</a> <a>ứ</a>
<a>Ừ</a> <a>ừ</a> <a>Ử</a> <a>ử</a> <a>Ữ</a> <a>ữ</a> <a>Ự</a> <a>ự</a> <a>Ỳ</a>
<a>ỳ</a> <a>Ỵ</a> <a>ỵ</a> <a>Ỷ</a> <a>ỷ</a> <a>Ỹ</a> <a>ỹ</a> <a>Ỻ</a> <a>ỻ</a>
<a>Ỽ</a> <a>ỽ</a> <a>Ỿ</a> <a>ỿ</a> 
</div></td></tr>
</table>

<hr />

<h3>Greek</h3>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover any missing glyphs in $fnt to display the Unicode character in FreeSerif.</div>
<table><tr><td><div class='font' style='text-align: left; '>
<a>Α</a> <a>α</a> <a>Β</a> <a>β</a> <a>Γ</a> <a>γ</a> <a>Δ</a> <a>δ</a> <a>Ε</a>
<a>ε</a> <a>Ζ</a> <a>ζ</a> <a>Η</a> <a>η</a> <a>Θ</a> <a>θ</a> <a>Ι</a> <a>ι</a>
<a>Κ</a> <a>κ</a> <a>Λ</a> <a>λ</a> <a>Μ</a> <a>μ</a> <a>Ν</a> <a>ν</a> <a>Ξ</a>
<a>ξ</a> <a>Ο</a> <a>ο</a> <a>Π</a> <a>π</a> <a>Ρ</a> <a>ρ</a> <a>Σ</a> <a>σ</a>/<a>ς</a>
<a>Τ</a> <a>τ</a> <a>Υ</a> <a>υ</a> <a>Φ</a> <a>φ</a> <a>Χ</a> <a>χ</a> <a>Ψ</a>
<a>ψ</a> <a>Ω</a> <a>ω</a>
</div></td></tr>
 </table>
 
 <hr />
 
<h3>Samaritan Hebrew</h3>
<table><tr><td><div class='font' style='text-align: right; '><a class= 'ex5'>
ࠀ	ࠁ	ࠂ	ࠃ	ࠄ	ࠅ	ࠆ	ࠇ	ࠈ	ࠉ	ࠊ	ࠋ	ࠌ	ࠍ	ࠎ	ࠏ	ࠐ	ࠑ	ࠒ	ࠓ	ࠔ	ࠕ	ࠖ	ࠗ	࠘	࠙	ࠚ	ࠛ	ࠜ	ࠝ	ࠞ	ࠟ	ࠠ	ࠡ	ࠢ	ࠣ	ࠤ	ࠥ	ࠦ	ࠧ	ࠨ	ࠩ	ࠪ	ࠫ	ࠬ	࠭	࠰	࠱	࠲	࠳	࠴	࠵	࠶	࠷	࠸	࠹	࠺	࠻	࠼	࠽	࠾
</a></div></td></tr>
</table>
 
</div>
<hr />
<img src='http://opensiddur.org/wp-content/images/Open-Siddur-Project-Logo.svg.png'>Unicode Hebrew Diacritic Support and Character Display Map by Aharon Varady, 2017-2024 and shared under an LGPL 3.0 Free Software license.<br /><a href='http://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=UnicodeBMPFallbackFont'>Unicode 6.1 BMP fallback font</a> shared by SIL with an SIL Open Font License 1.1.
</body>
"; 

} else {
    // Handle the case where $fnt is not set or is empty
    echo "<p>No font specified.</p>";
}
?>

</html>
