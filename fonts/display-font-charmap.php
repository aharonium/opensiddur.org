<html>
<?php
// Initialize $fnt to a default value
$fnt = '';

// Check if the 'fnt' parameter is set in the GET request
if (isset($_GET["fnt"])) {
    $fnt = $_GET["fnt"];
}

// Only allow plain font-folder names: letters, digits, space, underscore, hyphen, period, plus.
// Anything else (quotes, angle brackets, slashes, "..") is rejected, since $fnt is written
// straight into the page's HTML, CSS and font URLs.
if ($fnt !== '' && (!preg_match('/^[A-Za-z0-9 _.+-]+$/', $fnt) || strpos($fnt, '..') !== false)) {
    $fnt = '';
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
src: url('/wp-content/uploads/fonts/FallbackFont/AdobeBlack2VF.woff2') format('woff2'),
    url('/wp-content/uploads/fonts/FallbackFont/AdobeBlack2VF.woff') format('woff');
}

@font-face { font-family: 'HebrewReference';
src: url('/wp-content/uploads/fonts/SimpleCLM/SimpleCLM.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/SimpleCLM/SimpleCLM.woff') format('woff');
}

@font-face { font-family: 'SimpleCLM';
src: url('/wp-content/uploads/fonts/SimpleCLM/SimpleCLM.woff2') format('woff2'), 
    url('/wp-content/uploads/fonts/SimpleCLM/SimpleCLM.woff') format('woff');
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

a:hover { font-family: 'HebrewReference';
    text-decoration: none;
	color: blue;
}

a:active {
    text-decoration: none;
	color: #000000;
}

a.ex4:hover, a.ex4:active, a.ex4:visited {font-family: 'HebrewReference';}

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

.issue-missing { background-color: #d9d9d9; }
.issue-position { background-color: #f6d98b; }
table:not(.hide-issues) td.issue-missing a.ex4 { color: #9e9e9e;
 font-family: 'HebrewReference', 'Ezra SIL', 'SBL Hebrew', 'Noto Serif Hebrew', serif; }
table.hide-issues td.issue-missing, table.hide-issues td.issue-position { background-color: #eeeeee; }
.swatch { display: inline-block; width: 1.2em; border: 1px solid #000000; }

.colhead { font-family: 'SimpleCLM'; direction: rtl; font-size: 2em; }

.vanilla { font-family: 'SimpleCLM',sans-serif; direction: ltr; font-size: 0.8em; }
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
<!-- <a href='/wp-content/uploads/fonts/$fnt/$fnt.zip'>download $fnt.zip</a> -->
<p /><br />

Note: Unsupported characters and diacritics in $fnt are represented by a <a href='https://en.wikipedia.org/wiki/Fallback_font'>Blank Fallback Font</a>. Mouseover a cell to see the character displayed in a reference implementation.
<hr />
</div>

<div class='font'>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>Mouseover for reference implementation in Dyslexia Hebrew.</div>
<div class='vanilla' style='text-align: center; font-size: 0.5em;'>
<span class='swatch issue-missing'>&nbsp;</span> not yet supported in $fnt (shown in gray from the reference font) &nbsp;&nbsp;
<span class='swatch issue-position'>&nbsp;</span> mark may be misplaced (automatic check, worth a look) &nbsp;&nbsp;
<label><input type='checkbox' id='charmap-toggle' checked> highlight</label><br />
<span id='charmap-status'>Checking $fnt…</span>
</div>
<table id='charmap' data-fnt='$fnt'>
<thead><tr><th></th><th class='colhead' title='HEBREW POINT SHEVA (U+05B0)'><div>◌ְ</div></th><th class='colhead' title='HEBREW POINT SHEVA NA MUDGASH (U+05C8)'><div>◌׈</div></th><th class='colhead' title='HEBREW POINT HATAF SEGOL (U+05B1)'><div>◌ֱ</div></th><th class='colhead' title='HEBREW POINT HATAF PATAH (U+05B2)'><div>◌ֲ</div></th><th class='colhead' title='HEBREW POINT HATAF QAMATS (U+05B3)'><div>◌ֳ</div></th><th class='colhead' title='HEBREW POINT HIRIQ (U+05B4)'><div>◌ִ</div></th><th class='colhead' title='HEBREW POINT TSERE (U+05B5)'><div>◌ֵ</div></th><th class='colhead' title='HEBREW POINT SEGOL (U+05B6)'><div>◌ֶ</div></th><th class='colhead' title='HEBREW POINT PATAH (U+05B7)'><div>◌ַ</div></th><th class='colhead' title='HEBREW POINT QAMATS (U+05B8)'><div>◌ָ</div></th><th class='colhead' title='HEBREW POINT QAMATS QATAN (U+05C7)'><div>◌ׇ</div></th><th class='colhead' title='HEBREW POINT HOLAM (U+05B9)'><div>◌ֹ</div></th><th class='colhead' title='HEBREW POINT HOLAM HASER FOR VAV (U+05BA)'><div>◌ֺ</div></th><th class='colhead' title='HEBREW POINT QUBUTS (U+05BB)'><div>◌ֻ</div></th><th class='colhead' title='HEBREW POINT DAGESH OR MAPIQ (U+05BC)'><div>◌ּ</div></th><th class='colhead' title='HEBREW POINT DAGESH HAZAQ MUDGASH (U+05C9)'><div>◌׉</div></th><th class='colhead' title='HEBREW POINT METEG (U+05BD)'><div>◌ֽ</div></th><th class='colhead' title='HEBREW POINT RAFE (U+05BF)'><div>◌ֿ</div></th><th class='colhead' title='HEBREW POINT JUDEO-SPANISH VARIKA (U+FB1E)'><div>◌ﬞ</div></th><th class='colhead' title='HEBREW LETTER ALEF WITH PATAH (U+FB2E)&#10;HEBREW LIGATURE YIDDISH DOUBLE VAV (U+05F0)&#10;HEBREW LETTER YOD WITH HIRIQ (U+FB1D)'><div>◌</div></th><th class='colhead' title='HEBREW LETTER ALEF WITH QAMATS (U+FB2F)&#10;HEBREW LIGATURE YIDDISH VAV YOD (U+05F1)&#10;HEBREW LIGATURE YIDDISH YOD YOD PATAH (U+FB1F)'><div>◌</div></th><th class='colhead' title='HEBREW LIGATURE YIDDISH DOUBLE YOD (U+05F2)'><div>◌</div></th><th class='colhead' title='HEBREW PUNCTUATION GERESH (U+05F3)&#10;HEBREW PUNCTUATION GERSHAYIM (U+05F4)&#10;HEBREW YOD TRIANGLE (U+05EF)'><div>׳״</div></th><th class='colhead' title='HEBREW LIGATURE ALEF LAMED (U+FB4F)&#10;HEBREW PUNCTUATION NUN HAFUKHA (U+05C6)'><div>◌</div></th><th class='colhead' title='HEBREW LETTER WIDE ALEF (U+FB21)&#10;HEBREW LETTER WIDE DALET (U+FB22)&#10;HEBREW LETTER WIDE HE (U+FB23)&#10;HEBREW LETTER WIDE KAF (U+FB24)&#10;HEBREW LETTER WIDE LAMED (U+FB25)&#10;HEBREW LETTER WIDE FINAL MEM (U+FB26)&#10;HEBREW LETTER ALTERNATIVE AYIN (U+FB20)&#10;HEBREW LETTER WIDE RESH (U+FB27)&#10;HEBREW LETTER WIDE TAV (U+FB28)'><div>◌</div></th><th class='colhead' title='HEBREW MARK MASORA CIRCLE (U+05AF)'><div>◌֯</div></th><th class='colhead' title='HEBREW MARK UPPER DOT (U+05C4)'><div>◌ׄ</div></th><th class='colhead' title='HEBREW MARK LOWER DOT (U+05C5)'><div>◌ׅ</div></th></tr></thead>
 <tr><td><div class='font'><a class= 'ex4'>א</a></div></td><td><div class='font'><a class= 'ex4'>אְ</a></div></td><td><div class='font'><a class= 'ex4'>א׈</a></div></td><td><div class='font'><a class= 'ex4'>אֱ</a></div></td><td><div class='font'><a class= 'ex4'>אֲ</a></div></td><td><div class='font'><a class= 'ex4'>אֳ</a></div></td><td><div class='font'><a class= 'ex4'>אִ</a></div></td><td><div class='font'><a class= 'ex4'>אֵ</a></div></td><td><div class='font'><a class= 'ex4'>אֶ</a></div></td><td><div class='font'><a class= 'ex4'>אַ</a></div></td><td><div class='font'><a class= 'ex4'>אָ</a></div></td><td><div class='font'><a class= 'ex4'>אׇ</a></div></td><td><div class='font'><a class= 'ex4'>אֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>אֻ</a></div></td><td><div class='font'><a class= 'ex4'>אּ</a></div></td><td><div class='font'><a class= 'ex4'>א׉</a></div></td><td><div class='font'><a class= 'ex4'>אֽ</a></div></td><td><div class='font'><a class= 'ex4'>אֿ</a></div></td><td><div class='font'><a class= 'ex4'>אﬞ</a></div></td><td><div class='font'><a class= 'ex4'>אַ</a></div></td><td><div class='font'><a class= 'ex4'>אָ</a></div></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﭏ</a></div></td><td><div class='font'><a class= 'ex4'>ﬡ</a></div></td><td><div class='font'><a class= 'ex4'>א֯</a></div></td><td><div class='font'><a class= 'ex4'>אׄ</a></div></td><td><div class='font'><a class= 'ex4'>אׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ב</a></div></td><td><div class='font'><a class= 'ex4'>בְ</a></div></td><td><div class='font'><a class= 'ex4'>ב׈</a></div></td><td><div class='font'><a class= 'ex4'>בֱ</a></div></td><td><div class='font'><a class= 'ex4'>בֲ</a></div></td><td><div class='font'><a class= 'ex4'>בֳ</a></div></td><td><div class='font'><a class= 'ex4'>בִ</a></div></td><td><div class='font'><a class= 'ex4'>בֵ</a></div></td><td><div class='font'><a class= 'ex4'>בֶ</a></div></td><td><div class='font'><a class= 'ex4'>בַ</a></div></td><td><div class='font'><a class= 'ex4'>בָ</a></div></td><td><div class='font'><a class= 'ex4'>בׇ</a></div></td><td><div class='font'><a class= 'ex4'>בֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>בֻ</a></div></td><td><div class='font'><a class= 'ex4'>בּ</a></div></td><td><div class='font'><a class= 'ex4'>ב׉</a></div></td><td><div class='font'><a class= 'ex4'>בֽ</a></div></td><td><div class='font'><a class= 'ex4'>בֿ</a></div></td><td><div class='font'><a class= 'ex4'>בﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ב֯</a></div></td><td><div class='font'><a class= 'ex4'>בׄ</a></div></td><td><div class='font'><a class= 'ex4'>בׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ג</a></div></td><td><div class='font'><a class= 'ex4'>גְ</a></div></td><td><div class='font'><a class= 'ex4'>ג׈</a></div></td><td><div class='font'><a class= 'ex4'>גֱ</a></div></td><td><div class='font'><a class= 'ex4'>גֲ</a></div></td><td><div class='font'><a class= 'ex4'>גֳ</a></div></td><td><div class='font'><a class= 'ex4'>גִ</a></div></td><td><div class='font'><a class= 'ex4'>גֵ</a></div></td><td><div class='font'><a class= 'ex4'>גֶ</a></div></td><td><div class='font'><a class= 'ex4'>גַ</a></div></td><td><div class='font'><a class= 'ex4'>גָ</a></div></td><td><div class='font'><a class= 'ex4'>גׇ</a></div></td><td><div class='font'><a class= 'ex4'>גֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>גֻ</a></div></td><td><div class='font'><a class= 'ex4'>גּ</a></div></td><td><div class='font'><a class= 'ex4'>ג׉</a></div></td><td><div class='font'><a class= 'ex4'>גֽ</a></div></td><td><div class='font'><a class= 'ex4'>גֿ</a></div></td><td><div class='font'><a class= 'ex4'>גﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ג֯</a></div></td><td><div class='font'><a class= 'ex4'>גׄ</a></div></td><td><div class='font'><a class= 'ex4'>גׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ד</a></div></td><td><div class='font'><a class= 'ex4'>דְ</a></div></td><td><div class='font'><a class= 'ex4'>ד׈</a></div></td><td><div class='font'><a class= 'ex4'>דֱ</a></div></td><td><div class='font'><a class= 'ex4'>דֲ</a></div></td><td><div class='font'><a class= 'ex4'>דֳ</a></div></td><td><div class='font'><a class= 'ex4'>דִ</a></div></td><td><div class='font'><a class= 'ex4'>דֵ</a></div></td><td><div class='font'><a class= 'ex4'>דֶ</a></div></td><td><div class='font'><a class= 'ex4'>דַ</a></div></td><td><div class='font'><a class= 'ex4'>דָ</a></div></td><td><div class='font'><a class= 'ex4'>דׇ</a></div></td><td><div class='font'><a class= 'ex4'>דֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>דֻ</a></div></td><td><div class='font'><a class= 'ex4'>דּ</a></div></td><td><div class='font'><a class= 'ex4'>ד׉</a></div></td><td><div class='font'><a class= 'ex4'>דֽ</a></div></td><td><div class='font'><a class= 'ex4'>דֿ</a></div></td><td><div class='font'><a class= 'ex4'>דﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬢ</a></div></td><td><div class='font'><a class= 'ex4'>ד֯</a></div></td><td><div class='font'><a class= 'ex4'>דׄ</a></div></td><td><div class='font'><a class= 'ex4'>דׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ה</a></div></td><td><div class='font'><a class= 'ex4'>הְ</a></div></td><td><div class='font'><a class= 'ex4'>ה׈</a></div></td><td><div class='font'><a class= 'ex4'>הֱ</a></div></td><td><div class='font'><a class= 'ex4'>הֲ</a></div></td><td><div class='font'><a class= 'ex4'>הֳ</a></div></td><td><div class='font'><a class= 'ex4'>הִ</a></div></td><td><div class='font'><a class= 'ex4'>הֵ</a></div></td><td><div class='font'><a class= 'ex4'>הֶ</a></div></td><td><div class='font'><a class= 'ex4'>הַ</a></div></td><td><div class='font'><a class= 'ex4'>הָ</a></div></td><td><div class='font'><a class= 'ex4'>הׇ</a></div></td><td><div class='font'><a class= 'ex4'>הֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>הֻ</a></div></td><td><div class='font'><a class= 'ex4'>הּ</a></div></td><td><div class='font'><a class= 'ex4'>ה׉</a></div></td><td><div class='font'><a class= 'ex4'>הֽ</a></div></td><td><div class='font'><a class= 'ex4'>הֿ</a></div></td><td><div class='font'><a class= 'ex4'>הﬞ</a></div></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ה׳</a></div></td><td></td><td><div class='font'><a class= 'ex4'>ﬣ</a></div></td><td><div class='font'><a class= 'ex4'>ה֯</a></div></td><td><div class='font'><a class= 'ex4'>הׄ</a></div></td><td><div class='font'><a class= 'ex4'>הׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ו</a></div></td><td><div class='font'><a class= 'ex4'>וְ</a></div></td><td><div class='font'><a class= 'ex4'>ו׈</a></div></td><td><div class='font'><a class= 'ex4'>וֱ</a></div></td><td><div class='font'><a class= 'ex4'>וֲ</a></div></td><td><div class='font'><a class= 'ex4'>וֳ</a></div></td><td><div class='font'><a class= 'ex4'>וִ</a></div></td><td><div class='font'><a class= 'ex4'>וֵ</a></div></td><td><div class='font'><a class= 'ex4'>וֶ</a></div></td><td><div class='font'><a class= 'ex4'>וַ</a></div></td><td><div class='font'><a class= 'ex4'>וָ</a></div></td><td><div class='font'><a class= 'ex4'>וׇ</a></div></td><td><div class='font'><a class= 'ex4'>וֹ</a></div></td><td><div class='font'><a class= 'ex4'>וֺ</a></div></td><td><div class='font'><a class= 'ex4'>וֻ</a></div></td><td><div class='font'><a class= 'ex4'>וּ</a></div></td><td><div class='font'><a class= 'ex4'>ו׉</a></div></td><td><div class='font'><a class= 'ex4'>וֽ</a></div></td><td><div class='font'><a class= 'ex4'>וֿ</a></div></td><td><div class='font'><a class= 'ex4'>וﬞ</a></div></td><td><div class='font'><a class= 'ex4'>װ</a></div></td><td><div class='font'><a class= 'ex4'>ױ</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ו֯</a></div></td><td><div class='font'><a class= 'ex4'>וׄ</a></div></td><td><div class='font'><a class= 'ex4'>וׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ז</a></div></td><td><div class='font'><a class= 'ex4'>זְ</a></div></td><td><div class='font'><a class= 'ex4'>ז׈</a></div></td><td><div class='font'><a class= 'ex4'>זֱ</a></div></td><td><div class='font'><a class= 'ex4'>זֲ</a></div></td><td><div class='font'><a class= 'ex4'>זֳ</a></div></td><td><div class='font'><a class= 'ex4'>זִ</a></div></td><td><div class='font'><a class= 'ex4'>זֵ</a></div></td><td><div class='font'><a class= 'ex4'>זֶ</a></div></td><td><div class='font'><a class= 'ex4'>זַ</a></div></td><td><div class='font'><a class= 'ex4'>זָ</a></div></td><td><div class='font'><a class= 'ex4'>זׇ</a></div></td><td><div class='font'><a class= 'ex4'>זֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>זֻ</a></div></td><td><div class='font'><a class= 'ex4'>זּ</a></div></td><td><div class='font'><a class= 'ex4'>ז׉</a></div></td><td><div class='font'><a class= 'ex4'>זֽ</a></div></td><td><div class='font'><a class= 'ex4'>זֿ</a></div></td><td><div class='font'><a class= 'ex4'>זﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ז֯</a></div></td><td><div class='font'><a class= 'ex4'>זׄ</a></div></td><td><div class='font'><a class= 'ex4'>זׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ח</a></div></td><td><div class='font'><a class= 'ex4'>חְ</a></div></td><td><div class='font'><a class= 'ex4'>ח׈</a></div></td><td><div class='font'><a class= 'ex4'>חֱ</a></div></td><td><div class='font'><a class= 'ex4'>חֲ</a></div></td><td><div class='font'><a class= 'ex4'>חֳ</a></div></td><td><div class='font'><a class= 'ex4'>חִ</a></div></td><td><div class='font'><a class= 'ex4'>חֵ</a></div></td><td><div class='font'><a class= 'ex4'>חֶ</a></div></td><td><div class='font'><a class= 'ex4'>חַ</a></div></td><td><div class='font'><a class= 'ex4'>חָ</a></div></td><td><div class='font'><a class= 'ex4'>חׇ</a></div></td><td><div class='font'><a class= 'ex4'>חֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>חֻ</a></div></td><td><div class='font'><a class= 'ex4'>חּ</a></div></td><td><div class='font'><a class= 'ex4'>ח׉</a></div></td><td><div class='font'><a class= 'ex4'>חֽ</a></div></td><td><div class='font'><a class= 'ex4'>חֿ</a></div></td><td><div class='font'><a class= 'ex4'>חﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ח֯</a></div></td><td><div class='font'><a class= 'ex4'>חׄ</a></div></td><td><div class='font'><a class= 'ex4'>חׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ט</a></div></td><td><div class='font'><a class= 'ex4'>טְ</a></div></td><td><div class='font'><a class= 'ex4'>ט׈</a></div></td><td><div class='font'><a class= 'ex4'>טֱ</a></div></td><td><div class='font'><a class= 'ex4'>טֲ</a></div></td><td><div class='font'><a class= 'ex4'>טֳ</a></div></td><td><div class='font'><a class= 'ex4'>טִ</a></div></td><td><div class='font'><a class= 'ex4'>טֵ</a></div></td><td><div class='font'><a class= 'ex4'>טֶ</a></div></td><td><div class='font'><a class= 'ex4'>טַ</a></div></td><td><div class='font'><a class= 'ex4'>טָ</a></div></td><td><div class='font'><a class= 'ex4'>טׇ</a></div></td><td><div class='font'><a class= 'ex4'>טֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>טֻ</a></div></td><td><div class='font'><a class= 'ex4'>טּ</a></div></td><td><div class='font'><a class= 'ex4'>ט׉</a></div></td><td><div class='font'><a class= 'ex4'>טֽ</a></div></td><td><div class='font'><a class= 'ex4'>טֿ</a></div></td><td><div class='font'><a class= 'ex4'>טﬞ</a></div></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ט״ו</a></div></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ט֯</a></div></td><td><div class='font'><a class= 'ex4'>טׄ</a></div></td><td><div class='font'><a class= 'ex4'>טׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>י</a></div></td><td><div class='font'><a class= 'ex4'>יְ</a></div></td><td><div class='font'><a class= 'ex4'>י׈</a></div></td><td><div class='font'><a class= 'ex4'>יֱ</a></div></td><td><div class='font'><a class= 'ex4'>יֲ</a></div></td><td><div class='font'><a class= 'ex4'>יֳ</a></div></td><td><div class='font'><a class= 'ex4'>יִ</a></div></td><td><div class='font'><a class= 'ex4'>יֵ</a></div></td><td><div class='font'><a class= 'ex4'>יֶ</a></div></td><td><div class='font'><a class= 'ex4'>יַ</a></div></td><td><div class='font'><a class= 'ex4'>יָ</a></div></td><td><div class='font'><a class= 'ex4'>יׇ</a></div></td><td><div class='font'><a class= 'ex4'>יֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>יֻ</a></div></td><td><div class='font'><a class= 'ex4'>יּ</a></div></td><td><div class='font'><a class= 'ex4'>י׉</a></div></td><td><div class='font'><a class= 'ex4'>יֽ</a></div></td><td><div class='font'><a class= 'ex4'>יֿ</a></div></td><td><div class='font'><a class= 'ex4'>יﬞ</a></div></td><td><div class='font'><a class= 'ex4'>יִ</a></div></td><td><div class='font'><a class= 'ex4'>ײַ</a></div></td><td><div class='font'><a class= 'ex4'>ײ</a></div></td><td><div class='font'><a class= 'ex4'>ׯ</a></div></td><td></td><td></td><td><div class='font'><a class= 'ex4'>י֯</a></div></td><td><div class='font'><a class= 'ex4'>יׄ</a></div></td><td><div class='font'><a class= 'ex4'>יׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>כ</a></div></td><td><div class='font'><a class= 'ex4'>כְ</a></div></td><td><div class='font'><a class= 'ex4'>כ׈</a></div></td><td><div class='font'><a class= 'ex4'>כֱ</a></div></td><td><div class='font'><a class= 'ex4'>כֲ</a></div></td><td><div class='font'><a class= 'ex4'>כֳ</a></div></td><td><div class='font'><a class= 'ex4'>כִ</a></div></td><td><div class='font'><a class= 'ex4'>כֵ</a></div></td><td><div class='font'><a class= 'ex4'>כֶ</a></div></td><td><div class='font'><a class= 'ex4'>כַ</a></div></td><td><div class='font'><a class= 'ex4'>כָ</a></div></td><td><div class='font'><a class= 'ex4'>כׇּ</a></div></td><td><div class='font'><a class= 'ex4'>כֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>כֻ</a></div></td><td><div class='font'><a class= 'ex4'>כּ</a></div></td><td><div class='font'><a class= 'ex4'>כ׉</a></div></td><td><div class='font'><a class= 'ex4'>כֽ</a></div></td><td><div class='font'><a class= 'ex4'>כֿ</a></div></td><td><div class='font'><a class= 'ex4'>כﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬤ</a></div></td><td><div class='font'><a class= 'ex4'>כ֯</a></div></td><td><div class='font'><a class= 'ex4'>כׄ</a></div></td><td><div class='font'><a class= 'ex4'>כׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ך</a></div></td><td><div class='font'><a class= 'ex4'>ךְ</a></div></td><td><div class='font'><a class= 'ex4'>ך׈</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ךָ</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ךּ</a></div></td><td><div class='font'><a class= 'ex4'>ך׉</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ך֯</a></div></td><td><div class='font'><a class= 'ex4'>ךׄ</a></div></td><td><div class='font'><a class= 'ex4'>ךׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ל</a></div></td><td><div class='font'><a class= 'ex4'>לְ</a></div></td><td><div class='font'><a class= 'ex4'>ל׈</a></div></td><td><div class='font'><a class= 'ex4'>לֱ</a></div></td><td><div class='font'><a class= 'ex4'>לֲ</a></div></td><td><div class='font'><a class= 'ex4'>לֳ</a></div></td><td><div class='font'><a class= 'ex4'>לִ</a></div></td><td><div class='font'><a class= 'ex4'>לֵ</a></div></td><td><div class='font'><a class= 'ex4'>לֶ</a></div></td><td><div class='font'><a class= 'ex4'>לַ</a></div></td><td><div class='font'><a class= 'ex4'>לָ</a></div></td><td><div class='font'><a class= 'ex4'>לׇ</a></div></td><td><div class='font'><a class= 'ex4'>לֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>לֻ</a></div></td><td><div class='font'><a class= 'ex4'>לּ</a></div></td><td><div class='font'><a class= 'ex4'>ל׉</a></div></td><td><div class='font'><a class= 'ex4'>לֽ</a></div></td><td><div class='font'><a class= 'ex4'>לֿ</a></div></td><td><div class='font'><a class= 'ex4'>לﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬥ</a></div></td><td><div class='font'><a class= 'ex4'>ל֯</a></div></td><td><div class='font'><a class= 'ex4'>לׄ</a></div></td><td><div class='font'><a class= 'ex4'>לׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>מ</a></div></td><td><div class='font'><a class= 'ex4'>מְ</a></div></td><td><div class='font'><a class= 'ex4'>מ׈</a></div></td><td><div class='font'><a class= 'ex4'>מֱ</a></div></td><td><div class='font'><a class= 'ex4'>מֲ</a></div></td><td><div class='font'><a class= 'ex4'>מֳ</a></div></td><td><div class='font'><a class= 'ex4'>מִ</a></div></td><td><div class='font'><a class= 'ex4'>מֵ</a></div></td><td><div class='font'><a class= 'ex4'>מֶ</a></div></td><td><div class='font'><a class= 'ex4'>מַ</a></div></td><td><div class='font'><a class= 'ex4'>מָ</a></div></td><td><div class='font'><a class= 'ex4'>מׇ</a></div></td><td><div class='font'><a class= 'ex4'>מֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>מֻ</a></div></td><td><div class='font'><a class= 'ex4'>מּ</a></div></td><td><div class='font'><a class= 'ex4'>מ׉</a></div></td><td><div class='font'><a class= 'ex4'>מֽ</a></div></td><td><div class='font'><a class= 'ex4'>מֿ</a></div></td><td><div class='font'><a class= 'ex4'>מﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>מ֯</a></div></td><td><div class='font'><a class= 'ex4'>מׄ</a></div></td><td><div class='font'><a class= 'ex4'>מׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ם</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>םִ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬦ</a></div></td><td><div class='font'><a class= 'ex4'>ם֯</a></div></td><td><div class='font'><a class= 'ex4'>םׄ</a></div></td><td><div class='font'><a class= 'ex4'>םׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>נ</a></div></td><td><div class='font'><a class= 'ex4'>נְ</a></div></td><td><div class='font'><a class= 'ex4'>נ׈</a></div></td><td><div class='font'><a class= 'ex4'>נֱ</a></div></td><td><div class='font'><a class= 'ex4'>נֲ</a></div></td><td><div class='font'><a class= 'ex4'>נֳ</a></div></td><td><div class='font'><a class= 'ex4'>נִ</a></div></td><td><div class='font'><a class= 'ex4'>נֵ</a></div></td><td><div class='font'><a class= 'ex4'>נֶ</a></div></td><td><div class='font'><a class= 'ex4'>נַ</a></div></td><td><div class='font'><a class= 'ex4'>נָ</a></div></td><td><div class='font'><a class= 'ex4'>נׇ</a></div></td><td><div class='font'><a class= 'ex4'>נֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>נֻ</a></div></td><td><div class='font'><a class= 'ex4'>נּ</a></div></td><td><div class='font'><a class= 'ex4'>נ׉</a></div></td><td><div class='font'><a class= 'ex4'>נֽ</a></div></td><td><div class='font'><a class= 'ex4'>נֿ</a></div></td><td><div class='font'><a class= 'ex4'>נﬞ</a></div></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>׆</a></div></td><td></td><td><div class='font'><a class= 'ex4'>נ֯</a></div></td><td><div class='font'><a class= 'ex4'>נׄ</a></div></td><td><div class='font'><a class= 'ex4'>נׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ן</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ןָ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ן֯</a></div></td><td><div class='font'><a class= 'ex4'>ןׄ</a></div></td><td><div class='font'><a class= 'ex4'>ןׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ס</a></div></td><td><div class='font'><a class= 'ex4'>סְ</a></div></td><td><div class='font'><a class= 'ex4'>ס׈</a></div></td><td><div class='font'><a class= 'ex4'>סֱ</a></div></td><td><div class='font'><a class= 'ex4'>סֲ</a></div></td><td><div class='font'><a class= 'ex4'>סֳ</a></div></td><td><div class='font'><a class= 'ex4'>סִ</a></div></td><td><div class='font'><a class= 'ex4'>סֵ</a></div></td><td><div class='font'><a class= 'ex4'>סֶ</a></div></td><td><div class='font'><a class= 'ex4'>סַ</a></div></td><td><div class='font'><a class= 'ex4'>סָ</a></div></td><td><div class='font'><a class= 'ex4'>סׇ</a></div></td><td><div class='font'><a class= 'ex4'>סֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>סֻ</a></div></td><td><div class='font'><a class= 'ex4'>סּ</a></div></td><td><div class='font'><a class= 'ex4'>ס׉</a></div></td><td><div class='font'><a class= 'ex4'>סֽ</a></div></td><td><div class='font'><a class= 'ex4'>סֿ</a></div></td><td><div class='font'><a class= 'ex4'>סﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ס֯</a></div></td><td><div class='font'><a class= 'ex4'>סׄ</a></div></td><td><div class='font'><a class= 'ex4'>סׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ע</a></div></td><td><div class='font'><a class= 'ex4'>עְ</a></div></td><td><div class='font'><a class= 'ex4'>ע׈</a></div></td><td><div class='font'><a class= 'ex4'>עֱ</a></div></td><td><div class='font'><a class= 'ex4'>עֲ</a></div></td><td><div class='font'><a class= 'ex4'>עֳ</a></div></td><td><div class='font'><a class= 'ex4'>עִ</a></div></td><td><div class='font'><a class= 'ex4'>עֵ</a></div></td><td><div class='font'><a class= 'ex4'>עֶ</a></div></td><td><div class='font'><a class= 'ex4'>עַ</a></div></td><td><div class='font'><a class= 'ex4'>עָ</a></div></td><td><div class='font'><a class= 'ex4'>עׇ</a></div></td><td><div class='font'><a class= 'ex4'>עֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>עֻ</a></div></td><td><div class='font'><a class= 'ex4'>עּ</a></div></td><td><div class='font'><a class= 'ex4'>ע׉</a></div></td><td><div class='font'><a class= 'ex4'>עֽ</a></div></td><td><div class='font'><a class= 'ex4'>עֿ</a></div></td><td><div class='font'><a class= 'ex4'>עﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬠ</a></div></td><td><div class='font'><a class= 'ex4'>ע֯</a></div></td><td><div class='font'><a class= 'ex4'>עׄ</a></div></td><td><div class='font'><a class= 'ex4'>עׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>פ</a></div></td><td><div class='font'><a class= 'ex4'>פְ</a></div></td><td><div class='font'><a class= 'ex4'>פ׈</a></div></td><td><div class='font'><a class= 'ex4'>פֱ</a></div></td><td><div class='font'><a class= 'ex4'>פֲ</a></div></td><td><div class='font'><a class= 'ex4'>פֳ</a></div></td><td><div class='font'><a class= 'ex4'>פִ</a></div></td><td><div class='font'><a class= 'ex4'>פֵ</a></div></td><td><div class='font'><a class= 'ex4'>פֶ</a></div></td><td><div class='font'><a class= 'ex4'>פַ</a></div></td><td><div class='font'><a class= 'ex4'>פָ</a></div></td><td><div class='font'><a class= 'ex4'>פׇ</a></div></td><td><div class='font'><a class= 'ex4'>פֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>פֻ</a></div></td><td><div class='font'><a class= 'ex4'>פּ</a></div></td><td><div class='font'><a class= 'ex4'>פ׉</a></div></td><td><div class='font'><a class= 'ex4'>פֽ</a></div></td><td><div class='font'><a class= 'ex4'>פֿ</a></div></td><td><div class='font'><a class= 'ex4'>פﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>פ֯</a></div></td><td><div class='font'><a class= 'ex4'>פׄ</a></div></td><td><div class='font'><a class= 'ex4'>פׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ף</a></div></td><td><div class='font'><a class= 'ex4'>ףְ</a></div></td><td><div class='font'><a class= 'ex4'>ף׈</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ףּ</a></div></td><td><div class='font'><a class= 'ex4'>ף׉</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ף֯</a></div></td><td><div class='font'><a class= 'ex4'>ףׄ </a></div></td><td><div class='font'><a class= 'ex4'>ףׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>צ</a></div></td><td><div class='font'><a class= 'ex4'>צְ</a></div></td><td><div class='font'><a class= 'ex4'>צ׈</a></div></td><td><div class='font'><a class= 'ex4'>צֱ</a></div></td><td><div class='font'><a class= 'ex4'>צֲ</a></div></td><td><div class='font'><a class= 'ex4'>צֳ</a></div></td><td><div class='font'><a class= 'ex4'>צִ</a></div></td><td><div class='font'><a class= 'ex4'>צֵ</a></div></td><td><div class='font'><a class= 'ex4'>צֶ</a></div></td><td><div class='font'><a class= 'ex4'>צַ</a></div></td><td><div class='font'><a class= 'ex4'>צָ</a></div></td><td><div class='font'><a class= 'ex4'>צׇ</a></div></td><td><div class='font'><a class= 'ex4'>צֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>צֻ</a></div></td><td><div class='font'><a class= 'ex4'>צּ</a></div></td><td><div class='font'><a class= 'ex4'>צ׉</a></div></td><td><div class='font'><a class= 'ex4'>צֽ</a></div></td><td><div class='font'><a class= 'ex4'>צֿ</a></div></td><td><div class='font'><a class= 'ex4'>צﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>צ֯</a></div></td><td><div class='font'><a class= 'ex4'>צׄ</a></div></td><td><div class='font'><a class= 'ex4'>צׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ץ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ץ֯</a></div></td><td><div class='font'><a class= 'ex4'>ץׄ</a></div></td><td><div class='font'><a class= 'ex4'>ץׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ק</a></div></td><td><div class='font'><a class= 'ex4'>קְ</a></div></td><td><div class='font'><a class= 'ex4'>ק׈</a></div></td><td><div class='font'><a class= 'ex4'>קֱ</a></div></td><td><div class='font'><a class= 'ex4'>קֲ</a></div></td><td><div class='font'><a class= 'ex4'>קֳ</a></div></td><td><div class='font'><a class= 'ex4'>קִ</a></div></td><td><div class='font'><a class= 'ex4'>קֵ</a></div></td><td><div class='font'><a class= 'ex4'>קֶ</a></div></td><td><div class='font'><a class= 'ex4'>קַ</a></div></td><td><div class='font'><a class= 'ex4'>קָ</a></div></td><td><div class='font'><a class= 'ex4'>קׇ</a></div></td><td><div class='font'><a class= 'ex4'>קֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>קֻ</a></div></td><td><div class='font'><a class= 'ex4'>קּ</a></div></td><td><div class='font'><a class= 'ex4'>ק׉</a></div></td><td><div class='font'><a class= 'ex4'>קֽ</a></div></td><td><div class='font'><a class= 'ex4'>קֿ</a></div></td><td><div class='font'><a class= 'ex4'>קﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ק֯</a></div></td><td><div class='font'><a class= 'ex4'>קׄ</a></div></td><td><div class='font'><a class= 'ex4'>קׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ר</a></div></td><td><div class='font'><a class= 'ex4'>רְ</a></div></td><td><div class='font'><a class= 'ex4'>ר׈</a></div></td><td><div class='font'><a class= 'ex4'>רֱ</a></div></td><td><div class='font'><a class= 'ex4'>רֲ</a></div></td><td><div class='font'><a class= 'ex4'>רֳ</a></div></td><td><div class='font'><a class= 'ex4'>רִ</a></div></td><td><div class='font'><a class= 'ex4'>רֵ</a></div></td><td><div class='font'><a class= 'ex4'>רֶ</a></div></td><td><div class='font'><a class= 'ex4'>רַ</a></div></td><td><div class='font'><a class= 'ex4'>רָ</a></div></td><td><div class='font'><a class= 'ex4'>רׇ</a></div></td><td><div class='font'><a class= 'ex4'>רֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>רֻ</a></div></td><td><div class='font'><a class= 'ex4'>רּ</a></div></td><td><div class='font'><a class= 'ex4'>ר׉</a></div></td><td><div class='font'><a class= 'ex4'>רֽ</a></div></td><td><div class='font'><a class= 'ex4'>רֿ</a></div></td><td><div class='font'><a class= 'ex4'>רﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬧ</a></div></td><td><div class='font'><a class= 'ex4'>ר֯</a></div></td><td><div class='font'><a class= 'ex4'>רׄ</a></div></td><td><div class='font'><a class= 'ex4'>רׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>שׁ</a></div></td><td><div class='font'><a class= 'ex4'>שְׁ</a></div></td><td><div class='font'><a class= 'ex4'>שׁ׈</a></div></td><td><div class='font'><a class= 'ex4'>שֱׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֲׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֳׁ</a></div></td><td><div class='font'><a class= 'ex4'>שִׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֵׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֶׁ</a></div></td><td><div class='font'><a class= 'ex4'>שַׁ</a></div></td><td><div class='font'><a class= 'ex4'>שָׁ</a></div></td><td><div class='font'><a class= 'ex4'>שׇׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֹׁ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>שֻׁ</a></div></td><td><div class='font'><a class= 'ex4'>שּׁ</a></div></td><td><div class='font'><a class= 'ex4'>שׁ׉</a></div></td><td><div class='font'><a class= 'ex4'>שֽׁ</a></div></td><td><div class='font'><a class= 'ex4'>שֿׁ</a></div></td><td><div class='font'><a class= 'ex4'>שׁﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>שׁ֯</a></div></td><td><div class='font'><a class= 'ex4'>שׁׄ</a></div></td><td><div class='font'><a class= 'ex4'>שׁׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>שׂ</a></div></td><td><div class='font'><a class= 'ex4'>שְׂ</a></div></td><td><div class='font'><a class= 'ex4'>שׂ׈</a></div></td><td><div class='font'><a class= 'ex4'>שֱׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֲׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֳׂ</a></div></td><td><div class='font'><a class= 'ex4'>שִׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֵׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֶׂ</a></div></td><td><div class='font'><a class= 'ex4'>שַׂ</a></div></td><td><div class='font'><a class= 'ex4'>שָׂ</a></div></td><td><div class='font'><a class= 'ex4'>שׇׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֹׂ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>שֻׂ</a></div></td><td><div class='font'><a class= 'ex4'>שּׂ</a></div></td><td><div class='font'><a class= 'ex4'>שׂ׉</a></div></td><td><div class='font'><a class= 'ex4'>שֽׂ</a></div></td><td><div class='font'><a class= 'ex4'>שֿׂ</a></div></td><td><div class='font'><a class= 'ex4'>שׂﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>שׂ֯</a></div></td><td><div class='font'><a class= 'ex4'>שׂׄ</a></div></td><td><div class='font'><a class= 'ex4'>שׂׅ </a></div></td></tr>
 <tr><td><div class='font'><a class= 'ex4'>ת</a></div></td><td><div class='font'><a class= 'ex4'>תְ</a></div></td><td><div class='font'><a class= 'ex4'>ת׈</a></div></td><td><div class='font'><a class= 'ex4'>תֱ</a></div></td><td><div class='font'><a class= 'ex4'>תֲ</a></div></td><td><div class='font'><a class= 'ex4'>תֳ</a></div></td><td><div class='font'><a class= 'ex4'>תִ</a></div></td><td><div class='font'><a class= 'ex4'>תֵ</a></div></td><td><div class='font'><a class= 'ex4'>תֶ</a></div></td><td><div class='font'><a class= 'ex4'>תַ</a></div></td><td><div class='font'><a class= 'ex4'>תָ</a></div></td><td><div class='font'><a class= 'ex4'>תׇ</a></div></td><td><div class='font'><a class= 'ex4'>תֹ</a></div></td><td></td><td><div class='font'><a class= 'ex4'>תֻ</a></div></td><td><div class='font'><a class= 'ex4'>תּ</a></div></td><td><div class='font'><a class= 'ex4'>ת׉</a></div></td><td><div class='font'><a class= 'ex4'>תֽ</a></div></td><td><div class='font'><a class= 'ex4'>תֿ</a></div></td><td><div class='font'><a class= 'ex4'>תﬞ</a></div></td><td></td><td></td><td></td><td></td><td></td><td><div class='font'><a class= 'ex4'>ﬨ</a></div></td><td><div class='font'><a class= 'ex4'>ת֯</a></div></td><td><div class='font'><a class= 'ex4'>תׄ</a></div></td><td><div class='font'><a class= 'ex4'>תׅ </a></div></td></tr>
</table>
</div>

<hr />
<img src='http://opensiddur.org/wp-content/images/Open-Siddur-Project-Logo.svg.png'>Unicode Hebrew Diacritic Support and Character Display Map by Aharon Varady, 2017-present and shared under an LGPL 3.0 Free Software license. 
Last updated 2026.11.25.
";

echo <<<'CHARMAPJS'
<script>
/* Automatic check of each cell in the font being tested.

   Gray cell, gray character: something in the cell is not in the font. (In the font's own
   rendering the whole cluster falls back to the blank font, so the cell would otherwise
   look empty.) The gray character is the intended text, shown in the reference font.

   Amber cell: everything is in the font, but a mark breaks one of these rules:
     1. No mark may touch the letter.
     2. Marks below a letter sit centered under its lower half (yod and lamed: the whole
        letter); under resh, centered under its stem or spanning it; under gimel and dalet,
        anywhere from the letter's center to its stem. The lower dot, a Masoretic mark rather
        than a vowel, sits centered under the whole letter, resh included.
     3. Vowels below a letter all keep the same distance from the baseline (the font's usual
        gap), and never crowd it. The lower dot is exempt: it sits lower, at one height under
        every letter (final forms included), clear of the lowest vowel.
     4. Rafe, varika, masora circle and upper dot sit centered over the top of the letter.
        (Lamed is skipped: its top is its ascender.)
     4a. Holam on vav (U+05B9) sits centered over the vav or to the right, never to the left.
     4b. Holam haser for vav (U+05BA) sits to the left of the vav's stem, so the vav is read as
        a consonant (mitsvot, not mitsot).
     5. Dagesh / mapiq and dagesh hazaq sit inside the letter (or beside a narrow one).
   Hataf marks may center either the whole mark or just the vowel part.
   Letters with descenders (final kaf, final nun, final pe, final tsadi, qof) are checked
   only by rule 1, since their marks are placed beside or inside the descender.
   Meteg, standing alone under a letter, is centered like the vowels, with its own limit.
   Wide letterforms (U+FB21..U+FB28) that are no wider than the regular letter, and
   precomposed forms (alef with patah, yod with hiriq...) drawn as the plain letter, count as
   not yet supported (gray).
   Yiddish yod with hiriq (U+FB1D) keeps its hiriq tucked just under the yod, above the
   baseline; drawn below the baseline like a Hebrew hiriq, it is amber.
   Yiddish yod-yod-patah (U+FB1F): the patah sits above the baseline, centered under the two
   yods, and long enough to reach from one yod to the other.
   Qamats qatan has a longer stem and (QATAN_BAR_SHORTER) a shorter bar than qamats; a problem
   here marks the qamats qatan column header amber.
   Meteg under dalet sits under the stem. Marks under qof are centered under its bowl.
   Under ayin, a vowel may sit from the center of the letter's body to its right edge (fonts
   without an alternate ayin shift vowels right, clear of its leg). Hiriq under final mem
   (a missing yod, as in Yerushala-i-m) sits centered or toward the right.
   Sheva na (U+05C8) and dagesh hazaq (U+05C9) are new in Unicode 18.0. A browser, however
   recently built, ships its own copy of Unicode's character data, which can lag behind the
   standard. If that data doesn't include them yet, the browser doesn't attach them to the
   letter and shows them misplaced whatever the font does, so those two marks are not checked.
   The version is found by testing one character first added in each Unicode version against
   the JavaScript engine's data, which in practice matches the browser's text layout.
   If the font swaps in a different letter shape when the mark is added (VilnaSofer does
   this for ayin), the mark can't be isolated, so the cell is left unchecked and says so.
   These are geometric heuristics, so amber means "worth a look", not "certainly wrong".
   All distances are in em (fractions of the font size). */
(function () {
  var table = document.getElementById('charmap');
  var status = document.getElementById('charmap-status');
  var toggle = document.getElementById('charmap-toggle');
  if (!table || !document.fonts || !window.CanvasRenderingContext2D) {
    if (status) status.textContent = 'This browser cannot run the automatic check.';
    return;
  }
  if (toggle) toggle.addEventListener('change', function () {
    table.classList.toggle('hide-issues', !toggle.checked);
  });

  // ---- Tunable limits (em) ----
  var MIN_PIECE = 0.03;       // pieces of ink smaller than this share of the mark are noise
  var CENTER_LIMIT = 0.12;    // how far a centered mark may sit from its target (real problems so far: 0.10+)
  var MIN_BELOW_GAP = 0.01;   // smallest acceptable space between the baseline and a vowel below it
                              // (fonts tested: DejaVu Sans 0.02, VilnaSofer/FrankSofer 0.045, FreeSerif 0.06, Cardo 0.08)
  var GAP_VARIATION = 0.1;    // how far a vowel's gap may differ from the font's usual gap
  var LOWER_DOT_VARIATION = 0.02; // how far the lower dot's height may differ from letter to letter
  var LOWER_DOT_CLEAR = 0.03;     // smallest space between the lower dot and the lowest vowel below a letter
  var METEG_LIMIT = 0.12;     // how far a meteg may sit from center (good fonts: within 0.12 except
                              // under ayin and samekh in a few; Noto Sans/Serif/Rashi, Dorian: 0.12-0.6)
  var HOLAM_LEFT_LIMIT = 0.03;   // holam on vav: how far left of the vav's top it may sit
                                 // (good fonts: -0.005 to +0.095; Noto Serif -0.14, FiraGO -0.12, Dorian -0.07)
  var YY_PATAH_CENTER = 0.05;    // Yiddish yod-yod-patah (U+FB1F): how far off center the patah may sit
  var QATAN_BAR_SHORTER = true;  // also require qamats qatan's bar to be shorter than qamats's
                                 // (many fonts lengthen only the stem: Ezra SIL, Keter YG, FrankSofer...)
  var HASER_MIN_LEFT = 0.05;     // holam haser for vav: how far left of the vav's stem it must sit at least
                                 // (good fonts: 0.075 to 0.32; Noto Sans 0.005, i.e. centered like a holam)
  var OVERSHOOT = 0.02;       // ink this far below the baseline is a letter's normal overshoot
  var SHAPE_CHANGE = 0.05;    // share of the letter's ink that may vanish before we call it a swapped shape

  var FNT = table.getAttribute('data-fnt');
  var CHECKER_VERSION = '2026-11-24c';   // bump when the rules change, so stored results can be told apart
  var DEBUG = /[?&]debug=1\b/.test(location.search);   // add &debug=1 to the URL to see measurements in every tooltip
  var SIZE = 100, W = 360, H = 300, X = 290, Y = 180;
  var STACK = SIZE + 'px "' + FNT + '", "FallbackFont"';
  var BLANK = SIZE + 'px "FallbackFont"';
  var NBSP = ' ';

  function set(list) { var o = {}; list.forEach(function (c) { o[c] = 1; }); return o; }
  var BELOW = set([0x05B0, 0x05C8, 0x05B1, 0x05B2, 0x05B3, 0x05B4, 0x05B5, 0x05B6, 0x05B7,
                   0x05B8, 0x05C7, 0x05BB, 0x05BD, 0x05C5]);
  var ABOVE_CENTER = set([0x05BF, 0xFB1E, 0x05AF, 0x05C4]);
  var INSIDE = set([0x05BC, 0x05C9]);
  var NARROW = set([0x05D5, 0x05D6, 0x05D9, 0x05DF]);
  var DESCENDER = set([0x05DA, 0x05DF, 0x05E3, 0x05E5, 0x05E7]);
  var RESH = 0x05E8;
  var STEM_RANGE = set([0x05D2, 0x05D3]);      // gimel, dalet: from the center to the stem
  var LOWER_DOT = 0x05C5, METEG = 0x05BD, YIDDISH_YOD_HIRIQ = 0xFB1D, YIDDISH_YOD_YOD_PATAH = 0xFB1F;
  var QOF = 0x05E7, DALET = 0x05D3, QAMATS = 0x05B8, QAMATS_QATAN = 0x05C7;
  var AYIN = 0x05E2, FINAL_MEM = 0x05DD, HIRIQ = 0x05B4;
  var UNICODE18 = set([0x05C8, 0x05C9]);
  // Newest Unicode version the browser's character data includes, from one sample per version
  var UNICODE_SAMPLES = [['18.0', 0x05C8], ['17.0', 0x10940], ['16.0', 0x10D40], ['15.1', 0x2EBF0],
                         ['15.0', 0x11F00], ['14.0', 0x1E290], ['13.0', 0x10FB0]];
  var BROWSER_UNICODE = (function () {
    try {
      var unassigned = new RegExp('\\p{Cn}', 'u');
      for (var i = 0; i < UNICODE_SAMPLES.length; i++)
        if (!unassigned.test(String.fromCodePoint(UNICODE_SAMPLES[i][1]))) return UNICODE_SAMPLES[i][0];
      return null;
    } catch (e) { return null; }
  })();
  var BROWSER_HAS_U18 = BROWSER_UNICODE === '18.0';
  var UNICODE_DATA = BROWSER_UNICODE ? 'goes up to Unicode ' + BROWSER_UNICODE : 'is older than Unicode 13.0';
  function browserName() {
    var ua = navigator.userAgent, m;
    var known = [['Edg', 'Edge'], ['OPR', 'Opera'], ['Firefox', 'Firefox'], ['HeadlessChrome', 'Chromium (headless)'], ['Chrome', 'Chrome']];
    for (var i = 0; i < known.length; i++) {
      m = new RegExp(known[i][0] + '/(\\d+)').exec(ua);
      if (m) return known[i][1] + ' ' + m[1];
    }
    m = /Version\/(\d+).*Safari/.exec(ua);
    return m ? 'Safari ' + m[1] : 'this browser';
  }
  // Wide letterforms and the regular letter each should be wider than
  var WIDE = { 0xFB21: 0x05D0, 0xFB22: 0x05D3, 0xFB23: 0x05D4, 0xFB24: 0x05DB,
               0xFB25: 0x05DC, 0xFB26: 0x05DD, 0xFB27: 0x05E8, 0xFB28: 0x05EA };
  // Forms that must look different from the plain letter(s) they're built on
  var DIFFERS_FROM = { 0xFB20: 0x05E2, 0xFB2E: 0x05D0, 0xFB2F: 0x05D0, 0xFB1D: 0x05D9, 0xFB1F: 0x05F2 };
  var WHOLE = set([0x05D9, 0x05DC]);             // yod, lamed: center on the whole letter
  var HATAF = set([0x05B1, 0x05B2, 0x05B3]);
  var VAV = 0x05D5, HOLAM = 0x05B9, HOLAM_HASER = 0x05BA, LAMED = 0x05DC;

  function isMark(cp) {
    return (cp >= 0x0591 && cp <= 0x05BD) || cp === 0x05BF || cp === 0x05C1 || cp === 0x05C2 ||
           cp === 0x05C4 || cp === 0x05C5 || cp === 0x05C7 || cp === 0x05C8 || cp === 0x05C9 ||
           cp === 0xFB1E;
  }

  // Character names, taken from the column-header tooltips
  var names = {};
  Array.prototype.forEach.call(table.querySelectorAll('thead th[title]'), function (th) {
    th.getAttribute('title').split('\n').forEach(function (line) {
      var m = /^(.*) \(U\+([0-9A-F]{4,5})\)$/.exec(line.trim());
      if (m) names[parseInt(m[2], 16)] = m[1];
    });
  });
  function hex(cp) { var h = cp.toString(16).toUpperCase(); while (h.length < 4) h = '0' + h; return 'U+' + h; }
  function label(cp) { return (names[cp] ? names[cp] + ' ' : '') + '(' + hex(cp) + ')'; }
  function em(px) { return (px / SIZE).toFixed(2) + ' em'; }

  var canvas = document.createElement('canvas');
  canvas.width = W; canvas.height = H;
  var ctx = canvas.getContext('2d', { willReadFrequently: true });
  ctx.direction = 'rtl'; ctx.textAlign = 'right'; ctx.textBaseline = 'alphabetic';

  function ink(text, font) {
    ctx.clearRect(0, 0, W, H);
    ctx.font = font || STACK;
    ctx.fillText(text, X, Y);
    var d = ctx.getImageData(0, 0, W, H).data, mask = new Uint8Array(W * H), area = 0;
    var x0 = W, y0 = H, x1 = -1, y1 = -1;
    for (var i = 0, p = 3; i < W * H; i++, p += 4) {
      if (d[p] > 128) {
        mask[i] = 1; area++;
        var x = i % W, y = (i / W) | 0;
        if (x < x0) x0 = x; if (x > x1) x1 = x; if (y < y0) y0 = y; if (y > y1) y1 = y;
      }
    }
    return { mask: mask, area: area, box: area ? { x0: x0, y0: y0, x1: x1, y1: y1 } : null };
  }

  // Left and right edges of a letter's ink above the baseline (leaving out a leg or tail below it)
  function aboveBaseline(g) {
    var a0 = W, a1 = -1;
    for (var y = g.box.y0; y < Y; y++) for (var x = g.box.x0; x <= g.box.x1; x++)
      if (g.mask[y * W + x]) { if (x < a0) a0 = x; if (x > a1) a1 = x; }
    return a1 < 0 ? [g.box.x0, g.box.x1] : [a0, a1];
  }

  // Where a mark below should be centered
  // Center of the rightmost stroke reaching the bottom of a letter (the stem)
  function stemCenter(g) {
    var h = g.box.y1 - g.box.y0 + 1, band = Math.max(2, Math.round(h * 0.2));
    var hasInk = function (x) {
      for (var y = g.box.y1 - band + 1; y <= g.box.y1; y++) if (g.mask[y * W + x]) return true;
      return false;
    };
    var x1 = g.box.x1; while (x1 > g.box.x0 && !hasInk(x1)) x1--;
    var x0 = x1; while (x0 > g.box.x0 && hasInk(x0 - 1)) x0--;
    return (x0 + x1) / 2;
  }
  // Allowed range [from, to] for the center of a mark below
  function belowTarget(letter, g, mark) {
    var whole = (g.box.x0 + g.box.x1) / 2;
    if (mark === LOWER_DOT) {
      // The lower dot is a Masoretic mark, not a vowel: centered under the whole letter
      // (its ink above the baseline, so qof's leg doesn't pull it sideways), even under resh
      var ld = aboveBaseline(g), lc = (ld[0] + ld[1]) / 2;
      return [lc, lc];
    }
    if (letter === DALET && mark === METEG) { var ds = stemCenter(g); return [ds, ds]; }
    if (letter === QOF) {
      // Under the bowl: from the center of the ink above the baseline to a quarter of its width
      // right of it (good fonts set vowels under qof from 0.07 em left to 0.135 em right of center)
      var q = aboveBaseline(g), qc = (q[0] + q[1]) / 2;
      return [qc, qc + (q[1] - q[0]) / 4];
    }
    if (letter === AYIN) {
      // Fonts with an alternate ayin swap it in under a vowel (that cell is then left unchecked);
      // fonts without one (scribal fonts) shift the vowel right, clear of the leg that dips below
      // the baseline. So: from the center of the ayin's body to its right edge.
      var a = aboveBaseline(g);
      return [(a[0] + a[1]) / 2, a[1]];
    }
    if (letter === FINAL_MEM && mark === HIRIQ) {
      // Hiriq under final mem stands for a missing yod (Yerushala-i-m, \u05D9\u05E8\u05D5\u05E9\u05DC\u05DD\u05B4):
      // centered under the mem or toward the right, like a patah under a final het
      var fm = aboveBaseline(g);
      return [(fm[0] + fm[1]) / 2, fm[1]];
    }
    if (letter === RESH) { var s = stemCenter(g); return [s, s]; }
    if (STEM_RANGE[letter]) { var t = stemCenter(g); return [Math.min(whole, t), Math.max(whole, t)]; }
    if (WHOLE[letter]) return [whole, whole];
    // Otherwise the letter's lower half, so a flaring top (shin's arms, mem's hook,
    // vav's head) doesn't pull the target sideways
    var low = bandCenter(g, 'bottom', 0.5);
    return [low, low];
  }
  function distance(x, range) { return x < range[0] ? range[0] - x : x > range[1] ? x - range[1] : 0; }

  // Midpoint of the ink in a horizontal band of a glyph (top or bottom fifth of its height)
  function bandCenter(g, which, share) {
    var h = g.box.y1 - g.box.y0 + 1, band = Math.max(2, Math.round(h * (share || 0.2)));
    var ya = which === 'top' ? g.box.y0 : g.box.y1 - band + 1;
    var yb = which === 'top' ? g.box.y0 + band - 1 : g.box.y1;
    var xa = W, xb = -1;
    for (var y = ya; y <= yb; y++) for (var x = g.box.x0; x <= g.box.x1; x++)
      if (g.mask[y * W + x]) { if (x < xa) xa = x; if (x > xb) xb = x; }
    return (xa + xb) / 2;
  }

  var covered = {};
  function inFont(cp) {
    if (!(cp in covered)) {
      var ch = String.fromCodePoint(cp);
      covered[cp] = ink(isMark(cp) ? NBSP + ch : ch).area > 0;
    }
    return covered[cp];
  }

  // Connected ink (4-neighbour) from pixel i; notes whether it reaches any pixel of `other`
  function flood(mask, seen, i, other) {
    var stack = [i], px = [], joined = false;
    seen[i] = 1;
    while (stack.length) {
      var k = stack.pop(); px.push(k);
      if (other && other[k]) joined = true;
      var x = k % W;
      var nb = [x > 0 ? k - 1 : -1, x < W - 1 ? k + 1 : -1, k - W, k + W];
      for (var n = 0; n < 4; n++) {
        var q = nb[n];
        if (q >= 0 && q < W * H && mask[q] && !seen[q]) { seen[q] = 1; stack.push(q); }
      }
    }
    return { px: px, joined: joined };
  }
  // Merge pieces lying within 2 px of each other; return merged groups of at least minSize px
  function groups(comps, minSize) {
    var lab = new Int32Array(W * H).fill(-1), parent = comps.map(function (_, i) { return i; });
    function find(a) { while (parent[a] !== a) a = parent[a] = parent[parent[a]]; return a; }
    comps.forEach(function (px, id) { px.forEach(function (k) { lab[k] = id; }); });
    comps.forEach(function (px, id) {
      px.forEach(function (k) {
        var x = k % W, y = (k / W) | 0;
        for (var dy = -2; dy <= 2; dy++) for (var dx = -2; dx <= 2; dx++) {
          var xx = x + dx, yy = y + dy;
          if (xx < 0 || yy < 0 || xx >= W || yy >= H) continue;
          var o = lab[yy * W + xx];
          if (o >= 0 && o !== id) { var ra = find(id), rb = find(o); if (ra !== rb) parent[ra] = rb; }
        }
      });
    });
    var merged = {};
    comps.forEach(function (px, id) { var r = find(id); (merged[r] = merged[r] || []).push(px); });
    return Object.keys(merged).map(function (r) { return [].concat.apply([], merged[r]); })
                 .filter(function (px) { return px.length >= minSize; });
  }
  function pieces(mask, other, minSize) {
    var seen = new Uint8Array(W * H), out = [];
    for (var i = 0; i < W * H; i++) {
      if (mask[i] && !seen[i]) {
        var comp = flood(mask, seen, i, other);
        if (comp.px.length >= minSize) out.push(comp.px);
      }
    }
    return out;
  }

  // Measure one letter + single mark cell
  var letterInk = {};
  function measure(base, mark, text) {
    var letter = base.codePointAt(0);
    if (!letterInk[base]) letterInk[base] = { full: ink(base), bare: ink(String.fromCodePoint(letter)) };
    var b = letterInk[base].full, bare = letterInk[base].bare;
    var m = ink(NBSP + String.fromCodePoint(mark)), c = ink(text);
    if (!b.area || !m.area || !bare.area) return null;

    var lost = 0;
    for (var j = 0; j < W * H; j++) if (b.mask[j] && !c.mask[j]) lost++;
    if (lost > SHAPE_CHANGE * b.area) return { letter: letter, mark: mark, swapped: true };

    // Touching is judged by separate shapes, the way the eye does: draw the mark alone and
    // count its pieces (dots, bars), then count how many of them still stand free of the
    // letter's ink in the letter+mark rendering. A piece joined to the letter is touching it.
    // (Pixel-by-pixel comparisons proved unreliable: a mark drawn alone lands differently on
    // the pixel grid, and fonts often swap in a pre-drawn letter+mark glyph whose letter
    // differs slightly from the plain letter, leaving slivers that looked like contact.)
    // Pieces closer than 2 px count as one, so a mark whose own strokes nearly meet (qamats
    // qatan in some fonts) isn't split differently from one rendering to the next.
    var minPiece = Math.max(4, MIN_PIECE * m.area);
    var markPieces = groups(pieces(m.mask, null, 1), minPiece).length;
    var seen = new Uint8Array(W * H), freeComps = [], moAll = [];
    for (var i = 0; i < W * H; i++) {
      if (!c.mask[i] || b.mask[i]) continue;
      moAll.push(i);
      if (seen[i]) continue;
      var comp = flood(c.mask, seen, i, b.mask);
      if (!comp.joined) freeComps.push(comp.px);
    }
    var free = groups(freeComps, minPiece);
    var freePx = [].concat.apply([], free);
    // Touching: a piece of the mark has joined the letter, or most of its ink has
    // (the second catches a mark only partly joined to the letter)
    var touches = free.length < markPieces || freePx.length < 0.6 * m.area;
    var hidden = !free.length && moAll.length < 0.5 * m.area;
    // Measure the mark from its free pieces when they are all there; otherwise from every
    // pixel the mark adds to the letter
    var use = !touches && freePx.length ? freePx : moAll;
    var mo = { area: use.length, x0: W, y0: H, x1: -1, y1: -1 }, colTop = {};
    use.forEach(function (k) {
      var x = k % W, y = (k / W) | 0;
      if (x < mo.x0) mo.x0 = x; if (x > mo.x1) mo.x1 = x; if (y < mo.y0) mo.y0 = y; if (y > mo.y1) mo.y1 = y;
      if (!(x in colTop) || y < colTop[x]) colTop[x] = y;
    });
    // Separate pieces of the mark, left to right, by empty columns between them
    var cols = Object.keys(colTop).map(Number).sort(function (a, b2) { return a - b2; }), parts = [];
    cols.forEach(function (x) {
      var last = parts[parts.length - 1];
      if (last && x - last[1] <= 1) last[1] = x; else parts.push([x, x]);
    });
    var r = { parts: parts, letter: letter, mark: mark, freePieces: free.length, markPieces: markPieces,
              touches: touches || hidden, hidden: hidden, mo: mo, b: b, bare: bare };
    if (!mo.area) return r;
    r.cx = (mo.x0 + mo.x1) / 2; r.cy = (mo.y0 + mo.y1) / 2;

    // Clear space between the letter and a mark below it, column by column
    if (BELOW[mark]) {
      // Measured from the baseline, which is where designers set the vowel line. Round and
      // pointed bottoms dip slightly below the baseline by design (overshoot); that is ignored.
      // Only a real descender over the mark's columns (ayin's tail) is measured from its ink.
      var gap = Infinity, overshoot = Math.round(OVERSHOOT * SIZE);
      for (var col in colTop) {
        col = +col;
        var floor = Y - 1;
        for (var yy = colTop[col] - 1; yy > Y - 1 + overshoot; yy--) {
          if (bare.mask[yy * W + col]) { floor = yy; break; }
        }
        gap = Math.min(gap, colTop[col] - floor - 1);
      }
      r.gap = gap;
    }
    return r;
  }

  function problems(r, medianGap, lowerDot) {
    var out = [], L = r.letter, M = r.mark;
    r.metrics = ['pieces of the mark standing free of the letter: ' + r.freePieces + ' of ' + r.markPieces];
    if (r.hidden)
      out.push('the mark is drawn over the letter and can’t be seen');
    else if (r.touches)
      out.push('the mark touches the letter (' + (r.markPieces - r.freePieces) + ' of its ' + r.markPieces +
               (r.markPieces === 1 ? ' piece joins' : ' pieces join') + ' the letter’s ink)');
    if (!r.mo.area) return out;
    // The lower dot sits at one height under every letter (final forms included), low enough to
    // clear the lowest vowel the font draws below a letter
    if (M === LOWER_DOT && lowerDot && !r.touches) {
      var depth = r.mo.y0 - Y;
      if (lowerDot.usual != null && Math.abs(r.mo.y0 - lowerDot.usual) > LOWER_DOT_VARIATION * SIZE)
        out.push((r.mo.y0 > lowerDot.usual ? 'lower' : 'higher') + ' than the lower dot under the other letters (top ' +
                 em(depth) + ' below the baseline; usual ' + em(lowerDot.usual - Y) + ')');
      if (lowerDot.deepestVowel != null) {
        var clear = r.mo.y0 - lowerDot.deepestVowel - 1;
        r.metrics.push('clear of the lowest vowel by ' + em(clear) + ' (minimum ' + LOWER_DOT_CLEAR.toFixed(2) + ')');
        if (clear < LOWER_DOT_CLEAR * SIZE)
          out.push('not low enough to clear the vowels below letters (' + em(clear) + ' below the lowest vowel; minimum ' +
                   LOWER_DOT_CLEAR.toFixed(2) + ' em)');
      }
    }
    var tol = CENTER_LIMIT * SIZE;

    if (INSIDE[M]) {
      var bb = r.b.box, bw = bb.x1 - bb.x0 + 1;
      var slack = NARROW[L] ? Math.max(bw, 2 * (r.mo.x1 - r.mo.x0 + 1)) : 0;
      if (r.cx < bb.x0 - slack || r.cx > bb.x1 + slack || r.cy < bb.y0 || r.cy > bb.y1)
        out.push('the dot falls outside the letter');
    } else if (BELOW[M] && (!DESCENDER[L] || L === QOF)) {
      var target = belowTarget(L, r.bare, M);
      var where = M === LOWER_DOT ? 'under the letter' :
                  L === RESH || (L === DALET && M === METEG) ? 'under the stem' : L === QOF ? 'under the bowl' : 'under the letter';
      var off = distance(r.cx, target);
      if (HATAF[M] && r.parts.length > 1) {
        // A hataf may instead center its vowel part and let the sheva (its leftmost piece) trail
        var vowelCenter = (r.parts[1][0] + r.parts[r.parts.length - 1][1]) / 2;
        off = Math.min(off, distance(vowelCenter, target));
      }
      // Under resh, a wide mark (patah, qamats) may be shifted so that it spans the stem
      if (L === RESH && M !== LOWER_DOT && r.mo.x0 <= target[0] && target[0] <= r.mo.x1) off = 0;
      r.metrics.push('off-center ' + em(off) + ' (limit ' + (M === METEG ? METEG_LIMIT : CENTER_LIMIT).toFixed(2) + ')', 'gap ' + em(r.gap) +
                     (medianGap != null ? ' (usual ' + em(medianGap) + ')' : ''));
      var limit = M === METEG ? METEG_LIMIT : CENTER_LIMIT;
      if (off > limit * SIZE) {
        if (L === FINAL_MEM && M === HIRIQ && r.cx < target[0])
          out.push('the hiriq sits ' + em(off) + ' to the left of the final mem; it stands for a missing yod ' +
                   '(as in Yerushala-i-m), so it belongs under the mem, centered or toward the right');
        else if (L === AYIN)
          out.push('under ayin a vowel sits from the center of the letter to its right edge, clear of the leg; this one is ' +
                   em(off) + (r.cx < target[0] ? ' left of center' : ' beyond the right edge'));
        else
          out.push('not centered ' + where + ' (off by ' + em(off) + ', limit ' + limit.toFixed(2) + ' em)');
      }
      if (M !== LOWER_DOT && !DESCENDER[L]) {
        if (r.gap < MIN_BELOW_GAP * SIZE)
          out.push('too close to the letter (gap ' + em(r.gap) + ', minimum ' + MIN_BELOW_GAP.toFixed(3) + ' em)');
        else if (medianGap != null && Math.abs(r.gap - medianGap) > GAP_VARIATION * SIZE)
          out.push((r.gap > medianGap ? 'lower' : 'higher') + ' than the other vowels below letters in ' + FNT +
                   ' (gap ' + em(r.gap) + '; usual gap ' + em(medianGap) + ')');
      }
    } else if (L === VAV && M === HOLAM) {
      // Holam male: centered over the vav or to the right, never to the left (that is holam haser)
      var top = bandCenter(r.bare, 'top'), left = top - r.cx;
      r.metrics.push('left of the vav’s top by ' + em(left) + ' (limit ' + HOLAM_LEFT_LIMIT.toFixed(2) + ')');
      if (left > HOLAM_LEFT_LIMIT * SIZE)
        out.push('the holam sits ' + em(left) + ' left of the vav’s top; over a vav it belongs centered or to the right ' +
                 '(to the left is HOLAM HASER FOR VAV, U+05BA)');
    } else if (L === VAV && M === HOLAM_HASER) {
      // Holam haser for vav: to the left of the stem, so the vav stays a consonant (mitsvot, not mitsot)
      var stem = stemCenter(r.bare), lft = stem - r.cx;
      r.metrics.push('left of the vav’s stem by ' + em(lft) + ' (minimum ' + HASER_MIN_LEFT.toFixed(2) + ')');
      if (lft < HASER_MIN_LEFT * SIZE)
        out.push('the holam haser sits ' + (lft >= 0 ? em(lft) + ' left of' : em(-lft) + ' right of') +
                 ' the vav’s stem; it belongs to the left of the stem, at least ' + HASER_MIN_LEFT.toFixed(2) +
                 ' em, so it isn’t read as a holam male (mitsvot, not mitsot)');
    } else if (ABOVE_CENTER[M] && L !== LAMED) {
      var t = bandCenter(r.bare, 'top');
      r.metrics.push('off-center ' + em(Math.abs(r.cx - t)) + ' (limit ' + CENTER_LIMIT.toFixed(2) + ')');
      if (Math.abs(r.cx - t) > tol)
        out.push('not centered over the top of the letter (off by ' + em(Math.abs(r.cx - t)) + ')');
    }
    return out;
  }

  // Yiddish yod-yod-patah (U+FB1F): the patah sits just under the two yods, above the baseline,
  // centered, and reaching from the leg of one yod to the leg of the other (an ordinary patah is
  // often too short). A yod's leg is its lowest ink. Bars side by side at the patah's level (some
  // fonts draw a short patah under each yod) count together as the patah.
  function yodYodPatahProblems() {
    var lig = ink(String.fromCodePoint(YIDDISH_YOD_YOD_PATAH));
    if (!lig.box) return [];
    var parts = pieces(lig.mask, null, Math.max(4, 0.02 * lig.area)).map(function (px) {
      var b = { px: px, x0: W, y0: H, x1: -1, y1: -1 };
      px.forEach(function (k) { var x = k % W, y = (k / W) | 0;
        if (x < b.x0) b.x0 = x; if (x > b.x1) b.x1 = x; if (y < b.y0) b.y0 = y; if (y > b.y1) b.y1 = y; });
      return b;
    });
    var bars = parts.filter(function (b) { return (b.x1 - b.x0) > 2 * (b.y1 - b.y0); });
    if (!bars.length) return [];
    var lowest = bars.slice().sort(function (a, b) { return (b.y0 + b.y1) - (a.y0 + a.y1); })[0];
    var patahParts = bars.filter(function (b) { return b.y0 <= lowest.y1 && b.y1 >= lowest.y0; });
    var patah = { x0: Math.min.apply(null, patahParts.map(function (b) { return b.x0; })),
                  x1: Math.max.apply(null, patahParts.map(function (b) { return b.x1; })),
                  y1: Math.max.apply(null, patahParts.map(function (b) { return b.y1; })) };
    var yods = parts.filter(function (b) { return patahParts.indexOf(b) < 0; });
    if (!yods.length) return [];
    // Legs: runs of ink along the bottom row of each yod piece
    var legs = [];
    yods.forEach(function (b) {
      var run = null;
      for (var x = b.x0; x <= b.x1 + 1; x++) {
        var on = x <= b.x1 && lig.mask[b.y1 * W + x];
        if (on && !run) run = [x, x]; else if (on) run[1] = x;
        else if (run) { legs.push((run[0] + run[1]) / 2); run = null; }
      }
    });
    legs.sort(function (a, b) { return a - b; });
    var yx0 = Math.min.apply(null, yods.map(function (b) { return b.x0; })), yx1 = Math.max.apply(null, yods.map(function (b) { return b.x1; }));
    var out = [], off = (patah.x0 + patah.x1) / 2 - (yx0 + yx1) / 2, slack = 0.02 * SIZE;
    if (patah.y1 >= Y + 0.02 * SIZE)
      out.push('the patah sits ' + em(patah.y1 - Y + 1) + ' below the baseline, like a Hebrew patah under a single yod; ' +
               'in U+FB1F it sits just under the two yods, above the baseline');
    if (Math.abs(off) > YY_PATAH_CENTER * SIZE)
      out.push('the patah is ' + em(Math.abs(off)) + (off < 0 ? ' left' : ' right') + ' of center under the two yods');
    if (legs.length >= 2 && (patah.x0 > legs[0] + slack || patah.x1 < legs[legs.length - 1] - slack))
      out.push('the patah is too short to reach from the leg of one yod to the leg of the other (' +
               em(patah.x1 - patah.x0 + 1) + ' wide; the legs are ' + em(legs[legs.length - 1] - legs[0]) + ' apart)');
    return out;
  }

  // Qamats qatan: a longer stem and a shorter bar than the qamats (the bar is what is "small")
  function qamatsQatanProblems() {
    var q = ink(NBSP + String.fromCodePoint(QAMATS)), qq = ink(NBSP + String.fromCodePoint(QAMATS_QATAN));
    if (!q.box || !qq.box) return [];
    var same = q.area === qq.area;
    for (var i = 0; same && i < W * H; i++) if (q.mask[i] !== qq.mask[i]) same = false;
    if (same) return ['qamats qatan is drawn exactly like qamats'];
    var out = [], qw = q.box.x1 - q.box.x0 + 1, qh = q.box.y1 - q.box.y0 + 1;
    var tw = qq.box.x1 - qq.box.x0 + 1, th = qq.box.y1 - qq.box.y0 + 1;
    if (th <= qh) out.push('qamats qatan’s stem is no longer than qamats’s (' + em(th) + ' tall; qamats ' + em(qh) + ')');
    if (QATAN_BAR_SHORTER && tw >= qw) out.push('qamats qatan’s bar is no shorter than qamats’s (' + em(tw) + ' wide; qamats ' + em(qw) + ')');
    return out;
  }

  // A wide form no wider than its regular letter, or a special form drawn as the plain letter
  function formProblem(cp) {
    var g, plain;
    if (WIDE[cp]) {
      g = ink(String.fromCodePoint(cp)); plain = ink(String.fromCodePoint(WIDE[cp]));
      if (g.box && plain.box && (g.box.x1 - g.box.x0) < 1.1 * (plain.box.x1 - plain.box.x0))
        return label(cp) + ' is drawn no wider than the regular letter';
    } else if (DIFFERS_FROM[cp]) {
      g = ink(String.fromCodePoint(cp)); plain = ink(String.fromCodePoint(DIFFERS_FROM[cp]));
      var same = g.area === plain.area;
      for (var i = 0; same && i < W * H; i++) if (g.mask[i] !== plain.mask[i]) same = false;
      if (same) return label(cp) + ' is drawn the same as ' + label(DIFFERS_FROM[cp]);
    }
    return null;
  }

  function median(a) {
    if (!a.length) return null;
    a = a.slice().sort(function (x, y) { return x - y; });
    var k = a.length >> 1;
    return a.length % 2 ? a[k] : (a[k - 1] + a[k]) / 2;
  }

  function run() {
    if (ink('Aאָ', BLANK).area > 0) {
      fail('Automatic check unavailable: the blank fallback font did not load.');
      return;
    }
    var nMissing = 0, nPosition = 0, skippedU18 = 0, pending = [], gaps = [], findings = [],
        lowerDotTops = [], deepestVowel = null;
    var rows = table.querySelectorAll('tbody tr');
    Array.prototype.forEach.call(rows, function (tr) {
      var base = tr.cells[0].textContent.replace(/\s+/g, '');
      Array.prototype.forEach.call(tr.cells, function (td) {
        var a = td.querySelector('a');
        if (!a) return;
        var text = a.textContent.replace(/\s+/g, '');
        var cps = Array.from(text).map(function (ch) { return ch.codePointAt(0); });
        var missing = cps.filter(function (cp, i) { return cps.indexOf(cp) === i && !inFont(cp); });
        var why = missing.length ? missing.map(label).join(', ') : null;
        if (!why && cps.length === 1) why = formProblem(cps[0]);
        if (why) {
          td.classList.add('issue-missing');
          td.title = 'Not yet supported in ' + FNT + ': ' + why + '. Shown in gray in the reference font.';
          findings.push({ kind: missing.length ? 'missing' : 'form', text: text, why: why });
          nMissing++;
          return;
        }
        if (cps.length === 1 && cps[0] === YIDDISH_YOD_HIRIQ) {
          var yy = ink(text);
          if (yy.box && yy.box.y1 >= Y + 0.02 * SIZE) {
            td.classList.add('issue-position');
            td.title = 'Possible positioning problem: the hiriq sits ' + em(yy.box.y1 - Y + 1) + ' below the baseline, ' +
                       'like a Hebrew hiriq. In the Yiddish yod with hiriq (U+FB1D) it sits just under the yod, above the baseline.';
            findings.push({ kind: 'position', text: text, letter: 0x05D9, mark: 0x05B4, yiddish: true,
                            why: ['the hiriq sits ' + em(yy.box.y1 - Y + 1) + ' below the baseline, like a Hebrew hiriq'] });
            nPosition++;
          }
          return;
        }
        if (cps.length === 1 && cps[0] === YIDDISH_YOD_YOD_PATAH) {
          var yyp = yodYodPatahProblems();
          if (yyp.length) {
            td.classList.add('issue-position');
            td.title = 'Possible positioning problem: ' + yyp.join('; ') + '.';
            findings.push({ kind: 'position', text: text, special: 'Yiddish yud yud w/ pataḥ', why: yyp });
            nPosition++;
          }
          return;
        }
        if (!BROWSER_HAS_U18 && cps.some(function (cp) { return UNICODE18[cp]; })) {
          td.title = 'Not checked: ' + browserName() + '’s Unicode character data ' + UNICODE_DATA +
                     ' and doesn’t yet include this Unicode 18.0 mark, so the browser can’t attach it to the letter. ' +
                     'Check this column in a browser whose Unicode data includes 18.0.';
          skippedU18++;
          return;
        }
        if (text === base || text.indexOf(base) !== 0) return;
        var rest = Array.from(text.slice(base.length));
        if (rest.length !== 1 || !isMark(rest[0].codePointAt(0))) return;
        var r = measure(base, rest[0].codePointAt(0), text);
        if (!r) return;
        if (r.swapped) {
          td.title = 'Not checked automatically: ' + FNT + ' uses a different letter shape with this mark.';
          return;
        }
        pending.push({ td: td, r: r, text: text, sin: base.indexOf('\u05C2') >= 0 });
        if (r.gap != null && !DESCENDER[r.letter] && r.mark !== LOWER_DOT && !r.touches)
          gaps.push(r.gap);
        if (r.mo && r.mo.area && !r.touches) {
          if (r.mark === LOWER_DOT) lowerDotTops.push(r.mo.y0);
          else if (BELOW[r.mark]) deepestVowel = Math.max(deepestVowel == null ? -Infinity : deepestVowel, r.mo.y1);
        }
      });
    });
    var qatan = qamatsQatanProblems();
    if (qatan.length) {
      var qth = table.querySelector('thead th[title*="U+05C7"]');
      if (qth) { qth.classList.add('issue-position'); qth.title += '\n\nShape problem: ' + qatan.join('; ') + '.'; }
      findings.push({ kind: 'shape', special: 'qamats qatan shape', why: qatan });
    }
    var usualGap = median(gaps), measurements = [];
    var lowerDot = { usual: median(lowerDotTops), deepestVowel: deepestVowel };
    window.charmapMeasurements = measurements;
    pending.forEach(function (p) {
      var list = problems(p.r, usualGap, lowerDot);
      if (list.length) {
        p.td.classList.add('issue-position');
        p.td.title = 'Possible positioning problem: ' + list.join('; ') + '.';
        findings.push({ kind: 'position', text: p.text, letter: p.r.letter, mark: p.r.mark, sin: p.sin, why: list });
        nPosition++;
      }
      if (DEBUG) p.td.title = (p.td.title ? p.td.title + '\n' : '') + 'Measured: ' + p.r.metrics.join('; ');
      measurements.push({ text: p.td.textContent.trim(), mark: p.r.mark, letter: p.r.letter,
                          flagged: list, metrics: p.r.metrics });
    });
    status.textContent = nMissing + ' cells use characters not yet supported in ' + FNT + '; ' +
      nPosition + ' cells have marks that may be misplaced. Hover a highlighted cell for details.' +
      (qatan.length ? ' Qamats qatan (column header): ' + qatan.join('; ') + '.' : '') +
      (skippedU18 ? ' ' + browserName() + '’s Unicode character data ' + UNICODE_DATA +
                    ' and doesn’t yet include the Unicode 18.0 additions, so it can’t attach sheva na and dagesh ḥazaq ' +
                    'to their letters. They may look misplaced here whatever the font does; those ' + skippedU18 +
                    ' cells were not checked.' : '');
    table.setAttribute('data-check', 'done');
    report({ counts: { missing: nMissing, position: nPosition, uncheckedUnicode18: skippedU18 },
             summary: summarize(findings), findings: findings });
  }

  // ---- Results: a summary in the style of fonts.json's positioningErrors, plus the details ----
  var MARK_NAMES = { 0x05B0: 'sheva', 0x05C8: 'sheva na', 0x05B1: 'ḥataf segol', 0x05B2: 'ḥataf pataḥ',
    0x05B3: 'ḥataf qamats', 0x05B4: 'ḥiriq', 0x05B5: 'tsere', 0x05B6: 'segol', 0x05B7: 'pataḥ',
    0x05B8: 'qamats', 0x05C7: 'qamats qatan', 0x05B9: 'ḥolam', 0x05BA: 'ḥolam ḥaser for vav',
    0x05BB: 'qubutz', 0x05BC: 'dagesh', 0x05C9: 'dagesh ḥazaq', 0x05BD: 'meteg', 0x05BF: 'rafé',
    0xFB1E: 'varika', 0x05AF: 'masora circle', 0x05C4: 'upper dot', 0x05C5: 'lower dot' };
  var MARK_ORDER = [0x05B0, 0x05C8, 0x05B1, 0x05B2, 0x05B3, 0x05B4, 0x05B5, 0x05B6, 0x05B7, 0x05B8, 0x05C7,
    0x05B9, 0x05BA, 0x05BB, 0x05BC, 0x05C9, 0x05BD, 0x05BF, 0xFB1E, 0x05AF, 0x05C4, 0x05C5];
  var LETTER_NAMES = ['alef', 'bet', 'gimel', 'dalet', 'he', 'vav', 'zayin', 'ḥet', 'tet', 'yud',
    'khaf sofit', 'kaf', 'lamed', 'mem sofit', 'mem', 'nun sofit', 'nun', 'samekh', 'ayin', 'pe sofit',
    'pe', 'tsadi sofit', 'tsadi', 'qof', 'resh', 'shin', 'tav'];
  var GENERAL = 4;   // a mark misplaced under this many letters or more is reported by its name alone

  var GENERAL_MARKS = 6;   // a letter misplacing this many marks or more is reported as "niqqud w/ <letter>"

  function summarize(found) {
    var out = [];
    var pos = found.filter(function (f) { return f.kind === 'position' && !f.yiddish && !f.special; });
    pos.forEach(function (f) {
      f.letterName = LETTER_NAMES[f.letter - 0x05D0] || ('U+' + f.letter.toString(16).toUpperCase());
      if (f.letter === 0x05E9 && f.sin) f.letterName = 'sin';
      f.letterKey = f.letter === 0x05E9 ? 'shin/sin' : f.letterName;   // shin and sin count together
    });
    // 1. Marks misplaced under many letters are reported by name alone
    var lettersByMark = {};
    pos.forEach(function (f) { (lettersByMark[f.mark] = lettersByMark[f.mark] || {})[f.letterKey] = 1; });
    var generalMarks = MARK_ORDER.filter(function (m) {
      return lettersByMark[m] && Object.keys(lettersByMark[m]).length >= GENERAL;
    });
    // 2. Of what remains, a letter misplacing many marks is reported as "niqqud w/ <letter>"
    var rest = pos.filter(function (f) { return generalMarks.indexOf(f.mark) < 0; });
    var marksByLetter = {};
    rest.forEach(function (f) { (marksByLetter[f.letterKey] = marksByLetter[f.letterKey] || {})[f.mark] = 1; });
    var wholeLetters = Object.keys(marksByLetter).filter(function (l) {
      return Object.keys(marksByLetter[l]).length >= GENERAL_MARKS;
    });
    // 3. Everything else, as "<letter> w/ <mark>"
    MARK_ORDER.forEach(function (m) {
      if (generalMarks.indexOf(m) >= 0) { out.push(MARK_NAMES[m]); return; }
      var letters = [];
      rest.forEach(function (f) {
        if (f.mark === m && wholeLetters.indexOf(f.letterKey) < 0 && letters.indexOf(f.letterName) < 0) letters.push(f.letterName);
      });
      if (letters.indexOf('shin') >= 0 && letters.indexOf('sin') >= 0)
        letters = letters.filter(function (l) { return l !== 'sin'; }).map(function (l) { return l === 'shin' ? 'shin/sin' : l; });
      letters.forEach(function (l) { out.push(l + ' w/ ' + MARK_NAMES[m]); });
    });
    wholeLetters.forEach(function (l) { out.push('niqqud w/ ' + l); });
    if (found.some(function (f) { return f.yiddish; })) out.push('Yiddish yud w/ ḥiriq');
    found.forEach(function (f) { if (f.special && out.indexOf(f.special) < 0) out.push(f.special); });
    return out;
  }

  function report(result) {
    result.font = FNT;
    result.checkedAt = new Date().toISOString();
    result.browser = browserName();
    result.browserUnicode = BROWSER_UNICODE;
    result.checkerVersion = CHECKER_VERSION;
    window.charmapResults = result;
    if (window.parent && window.parent !== window) {
      try { window.parent.postMessage({ type: 'charmap-results', results: result }, '*'); } catch (e) {}
    }
    if (status && !result.error) {
      var link = document.createElement('a');
      link.href = URL.createObjectURL(new Blob([JSON.stringify(result, null, 2)], { type: 'application/json' }));
      link.download = FNT + '-positioning.json';
      link.textContent = 'Download these results (JSON)';
      link.style.textDecoration = 'underline';
      status.appendChild(document.createElement('br'));
      status.appendChild(link);
    }
  }
  function fail(message) {
    status.textContent = message;
    report({ error: message });
  }

  var sample = table.textContent;
  Promise.all([
    document.fonts.load(STACK, sample),
    document.fonts.load(BLANK, 'A')
  ]).then(function (loaded) {
    if (!loaded[0].length) {
      fail('Automatic check unavailable: ' + FNT + ' did not load.');
      return;
    }
    requestAnimationFrame(run);
  }, function () {
    fail('Automatic check unavailable: the fonts could not be loaded.');
  });
})();
</script>
CHARMAPJS;

echo "</body>
"; 

} else {
    // Handle the case where $fnt is not set or is empty
    echo "<p>No font specified.</p>";
}
?>

</html>