---
ID: 7944
post_title: Transliteration
post_name: transliterate
author: the Hierophant
post_date: 2013-12-19 11:03:16
layout: page
link: >
  http://opensiddur.org/help/transliterate/
published: true
tags: [ ]
categories: [ ]
---
[caption id="attachment_9072" align="alignright" width="325"]<a href="http://opensiddur.org/wp-content/uploads/2013/12/transliterate-smaller-e1402671137301.png"><img src="http://opensiddur.org/wp-content/uploads/2013/12/transliterate-smaller-e1402671137301.png" alt="&quot;How do you transliterate?&quot; by Aharon Varady (CC-BY-SA)" width="325" height="325" class="size-full wp-image-9072" /></a> "How do you transliterate?" by Aharon Varady (CC-BY-SA)[/caption]

Part of our project of digitizing Jewish liturgy is to provide a resource to convert the consonants and vowels of Hebrew into any other script. Ultimately this will be a standard feature in the web application we are building to help folk craft their own siddur, machzor, bentscher or other useful prayer book. Our lead developer, Efraim Feinstein, recently managed to put most of the pieces together to accomplish this, a milestone for the Open Siddur Project. 

<h3>Romanization schemas</h3>

There is no single standard for Hebrew transliteration. In our demo you can transliterate Hebrew text in eight different ways originally set out in the following sources:
<ul>
        <li><a class="pdf" href="http://web.archive.org/web/20120308103431/http://hebrew-academy.huji.ac.il/hahlatot/TheTranscription/Documents/taatiq2007.pdf">Rules of Transcription from Hebrew Script to Latin Script</a> (<a href="http://web.archive.org/web/20100724065445/http://hebrew-academy.huji.ac.il:80/hahlatot/TheTranscription/Pages/taatiq.aspx">Academy of the Hebrew Language</a>, 2007)</li>
        <li><a class="pdf" href="https://en.wikipedia.org/wiki/File:IPA_chart_%28C%292005.pdf">International Phonetic Alphabet</a> (2005, as used by <a href="http://en.wikipedia.org/wiki/Wikipedia:IPA_for_Hebrew">Wikipedia</a>)
	<li><a class="pdf" href="http://opensiddur.org/wp-content/uploads/2010/07/SBL-Handbook-of-Style-Transliterating-and-Transcribing-Ancient-Texts.pdf">The SBL Handbook of Style</a> (<a href="http://www.sbl-site.org/publications/publishingwithsbl.aspx">Society of Biblical Literature</a>, 1999)</li>
        <li><a class="pdf" href="http://www.loc.gov/catdir/cpso/romanization/hebrew.pdf">Romanization Table for Hebrew and Yiddish</a> (<a href="http://www.loc.gov/catdir/cpso/roman.html">The American Library Association/Library of Congress</a>, 1997)</li>
	<li><a class="pdf" href="http://www.heraldmag.org/olb/Contents/dictionaries/SHebrew.pdf">Concise Dictionary of the Words in the Hebrew Bible with their Renderings</a> (James Strong, 1890)</li>
	<li><a href="http://ccat.sas.upenn.edu/beta/key.html">Coding for Transliteration of Hebrew</a> (<a href="http://www.wts.edu/resources/alangroves/grovesprojects.html">Michigan-Claremont</a>, 1984)</li>
        <li>An approximation of Modern Israeli Hebrew pronunciation by Open Siddur lead developer, Efraim Feinstein (2010)</li>
        <li>An approximation of Modern Ashkenazi Hebrew pronunciation by Aharon Varady (2010)</li>
</ul>

Currently, the demonstration only provides <a href="http://en.wikipedia.org/wiki/Romanization_of_Hebrew">romanization</a> -- the transliteration of Hebrew to a Latin script. By incorporating additional transliteration standards for additional scripts, we will be able to convert Hebrew to Greek, Cyrillic, Amharic, etc. (and vice versa). The tables are not fixed, and we can change them if bugs are found or better ways are suggested.  Eventually, we will be implementing a table editor to allow editing the tables, creating, and of course, sharing new ones. For now, if you would like to add a transliteration standard to our database, take a look first at <a href="http://web.archive.org/web/20160504154729/http://jewishliturgy.googlecode.com:80/svn/branches/efraim/data/global/transliteration/">these examples</a>.

<h3>Transliteration Tool</h3>

The source code for this romanizing transliterator is open source, LGPL licensed, so you are free to take this and use it in your web application or website as well. Join us, and help make this a spectacular resource for everyone. 
<hr>

The form below provides a demonstration of this open source technology. Try it with some Hebrew now! If you don't have any handy, try transliterating this phrase from the opening of the Amidah: 
<blockquote>
<div class="hebrew" style="text-align:right; font-size: x-large;"><span lang="he">אֲדֹנָי שְׂפָתַי תִּפְתָּח וּפִי יַגִּיד תְּהִלָּתֶךָ׃</span></div>
</blockquote>
or this verse from Tzephaniah, with all the letters of the aleph-bet:
<blockquote>
<div class="hebrew" style="text-align:right; font-size: x-large;"><span lang="he">
לָכֵן חַכּוּ לִי נְאֻם יְהוָה לְיוֹם קוּמִי לְעַד כִּי מִשְׁפָּטִי לֶאֱסֹף גּוֹיִם לְקָבְצִי מַמְלָכוֹת לִשְׁפֹּךְ עֲלֵיהֶם זַעְמִי כֹּל חֲרוֹן אַפִּי כִּי בְּאֵשׁ קִנְאָתִי תֵּאָכֵל כָּל הָאָרֶץ׃
</span></div>
</blockquote>

<iframe src="http://app.opensiddur.org/exist/apps/opensiddur-demos/translit/index.html" width="600" height="1000" frameborder="0" marginheight="0" marginwidth="0" scrolling="no">Loading...</iframe>