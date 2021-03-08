<html>
<head></head>
<body>
Title: Trōpifying English and other Latin Script Language Readings with Masoretic Hebrew cantillation marks (t'amim, trōp)<br />
Primary contributor: aharon.varady<br />
For attribution and license, please consult the following URL: <a href="http://opensiddur.org/?p=35573">http://opensiddur.org/?p=35573</a>
<p />
<hr />

<strong>DOWNLOAD:</strong> <a href="https://opensiddur.org/wp-content/uploads/fonts/LatinTrop/LatinTrop.zip">LatinTrop.ttf</a>

<div class="latintrop" style="margin-left: auto; margin-right: auto; text-align: center;"><span style="font-size: x-large; text-align: center;">
qadmañ munaâḥ zarqaè munaâḥ segoél munaâḥ | munaâḥ rəviiì mapaàkh pashtaá zaqef-qataãn
zaqef-gadoíl merkhaä tipḥaå munaâḥ etnaḥtaæ pazeðr təlisha-qətanaóh
təlisha gədolaôh qadmañ vəazlaò azlaò-geresh gershayîim
dargaê təviër ïyətiv pəsiq | merkhaä tipḥaå merkhaä sof pasuçq ׃
shalsheõlet merkha kəfulaöh yeraḥ ben yo÷mo qarnei faraøh ׃
</span></div>
&nbsp;

<hr />

Some rabbis, cantors, and other learned Jews have been adding cantillation to English readings and translations for about three decades now, innovated largely within the world of Jewish Renewal influenced congregations. (Find, for example, the work of <a href="/profile/len-fellman/">Len Fellman</a>, Ḥazan <a href="/profile/yakov-kessler/">Jack Kessler</a>, and more recently, Rabbi <a href="/profile/david-evan-markus/">David Evan Markus</a>.) For technical reasons, this practice has largely been realized by hand, with a marker or pencil, on a printout. On the digital side, Len Fellman created a set of images of trōp which he painstakingly places above and below the text of his <em>transtrōpilations</em> (translations of the Masoretic text of the parashot and haftarot with trōp!).

Here at the Open Siddur, we've worked to archive English texts with trōp with a goal of preserving them in some easily reproducible, copy/paste and indexable, future-proof standard. To this end, I first attempted to transcribe the text and then apply the trōp from the Unicode Hebrew code range directly within the English. This <em>sort of</em> worked -- the issues being one of legibility. The trōp were never really sized properly <em>vis a vis</em> the English text, and, more importantly, the trōp didn't appear visually in the proper direction of the text. What was needed was some means to provide a mirror image of the trōp glyphs when displaying them in left-to-right oriented text. Ideally, it would be wonderful if such a technical solution existed. Alas, no! Another solution would be to have a new set of Unicode trōp glyphs to use for just such an purpose. Alas, such trōp do not exist in the current Unicode standard either. So what can be done?

The good news is that something has been done already. The trōp table above displays a font developed in 2010 by Jonathan Salzedo for cantillating (trōpifying) Latin script text with Masoretic Hebrew cantillation marks (t'amim). The font is not so sophisticated; Salzedo used off-the-shelf, closed-source font software for his project (<a href="https://www.high-logic.com/software/font-creator">Font Creator</a>), rather than say, <a href="https://fontforge.org">FontForge</a>. His font simply takes existing Latin letters with diacritical marks, ligatures, and symbols, and maps these to new, hybrid Latin-Masoretic Hebrew glyphs.

So, for example, "aá" and "aã" become <span class="latintrop">aá</span> and <span class="latintrop">aã</span>.

While not a perfect solution for preserving English texts with trōp under a future-proof standard, this innovation moves us in that direction. What is important is that the font can be improved upon, and a means of converting texts from the standard introduced here to something more robust, while non-trivial, seems very possible.

Documentation for the font being sparse, I've prepared the following which should help.

<blockquote><p style="padding-left: 40px;">To use this font in a document, you'll first need to <a href="/wp-content/uploads/fonts/LatinTrop/LatinTrop.zip">download the ZIP file</a>, unpack it, and install the fonts.
Then, select the font in your preferred text layout application (e.g. LibreOffice Writer).</p>

<p style="padding-left: 40px;">This, then, is the crucial instruction:
&nbsp;
To get the trōp you need to display,
type the letter you want the trōp to appear adjacent to (above or below)
and then type the particular character associated with the trōp in the font.</p>
</blockquote>

I've provided an alphabetical index of the trōp below. The index also shows the particular combinations required to display the trōp with the font using the trōp names as an example.

Many thanks to Len Fellman for bringing this font to our attention. --Aharon Varady

&nbsp;

<hr />

<blockquote><h2>Index of Associated Characters and Trōp</h2>
<div class="english-sans" style="font-size: 1.5em; padding-left: 40px;">
For <span class="latintrop">◌ò (azlaò)</span> use <strong>ò</strong>, for example: 'azlaò'
For <span class="latintrop">◌ê (dargaê)</span> use <strong>ê</strong>, for example: 'dargaê'
For <span class="latintrop">◌æ (etnaḥtaæ)</span> use <strong>æ</strong>, for example: 'etnaḥtaæ'
For <span class="latintrop">◌î (gershayîim)</span> use <strong>î</strong>, for example: 'gershayîim'
For <span class="latintrop">◌ö (kəfulaöh)</span> use <strong>ö</strong>, for example: 'kəfulaöh'
For <span class="latintrop">◌à (mapaàkh)</span> use <strong>à</strong>, for example: 'mapaàkh'
For <span class="latintrop">◌ä (merkhaä)</span> use <strong>ä</strong>, for example: 'merkhaä'
For <span class="latintrop">◌â (munaâḥ)</span> use <strong>â</strong>, for example: 'munaâḥ'
For <span class="latintrop">◌á (pashtaá)</span> use <strong>á</strong>, for example: 'pashtaá'
For <span class="latintrop">◌ð (pazeðr)</span> use <strong>ð</strong>, for example: 'pazeðr'
For <span class="latintrop">◌ñ (qadmañ)</span> use <strong>ñ</strong>, for example: 'qadmañ'
For <span class="latintrop">◌ø (qarnei faraøh)</span> use <strong>ø</strong>, for example: 'qarnei faraøh'
For <span class="latintrop">◌ì (rəviiì)</span> use <strong>ì</strong>, for example: 'rəviiì'
For <span class="latintrop">◌é (segoél)</span> use <strong>é</strong>, for example: 'segoél'
For <span class="latintrop">◌õ (shalsheõlet)</span> use <strong>õ</strong>, for example: 'shalsheõlet'
For <span class="latintrop">◌ç (sof pasuçq)</span> use <strong>ç</strong>, for example: 'sof pasuçq'
For <span class="latintrop">◌ó (təlisha-qətanaóh)</span> use <strong>ó</strong>, for example: 'təlisha-qətanaóh'
For <span class="latintrop">◌ô (təlisha gədolaôh)</span> use <strong>ô</strong>, for example: 'təlisha gədolaôh'
For <span class="latintrop">◌ë (təviër)</span> use <strong>ë</strong>, for example: 'təviër'
For <span class="latintrop">◌å (tipḥaå)</span> use <strong>å</strong>, for example: 'tipḥaå'
For <span class="latintrop">◌ï (ïyətiv pəsiq)</span> use <strong>ï</strong>, for example: 'ïyətiv pəsiq'
For <span class="latintrop">◌÷ (yeraḥ ben yo÷mo)</span> use <strong>÷</strong>, for example: 'yeraḥ ben yo÷mo'
For <span class="latintrop">◌ã (zaqef-qataãn)</span> use <strong>ã</strong>, for example: 'zaqef-qataãn'
For <span class="latintrop">◌í (zaqef-gadoíl)</span> use <strong>í</strong>, for example: 'zaqef-gadoíl'
For <span class="latintrop">◌è (zarqaè)</span> use <strong>è</strong>, for example: 'zarqaè'
</div></blockquote>

&nbsp;
</body>
</html>