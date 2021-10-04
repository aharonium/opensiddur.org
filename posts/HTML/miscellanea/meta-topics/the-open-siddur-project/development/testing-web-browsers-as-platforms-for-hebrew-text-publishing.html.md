<html>
<head></head>
<body>
Title: Testing Web browsers as Platforms for Hebrew Text Publishing<br />
Primary contributor: aharon-nissn.varady<br />
For attribution and license, please consult the following URL: <a href="http://opensiddur.org/?p=4262">http://opensiddur.org/?p=4262</a>
<p />
<hr />

Given that one important aspiration of the Open Siddur Project is the development of a web application for anyone to edit, maintain, and share the content of a personal prayerbook that they can craft online, I'm very concerned at how well web browsers today display the Hebrew language with all of its diacritical (vowels, cantillation) marks. Indeed, the Open Siddur Project has an international scope, so ostensibly, we wish to support text in every language Jews speak or have ever spoken liturgy or liturgy-related text (the creative content of Jewish spiritual practice). Combine a digital font or fonts that support the full range of human written languages with a platform that correctly displays such fonts, and you have one basis for an excellent potential collaborative publishing platform.

So for the last year, I've been working on <a href="http://aharon.varady.net/browser-test/">a series of tests</a> to determine how well some popular and some less well-known web browsers perform in supporting the technology for displaying Hebrew text. In particular, I'm interested to see which browsers are failing to use a web standard called CSS @font-face to properly display Unicode Hebrew fonts that support the full range of Hebrew diacritics and which contain excellent font logic for diacritical positioning. I'm also keen on seeing which browsers might even be failing at recognizing bidirectional (BIDI) and right-to-left (RTL) text, given that Hebrew is read RTL and it's not uncommon to find עִבְרִית</span> and other left-to-right (LTR) languages written together with one another.

With these tests I also hoped to find some simple way by which an individual browsing the web could troubleshoot whether the problem is in their browser, their browser's settings, or in a web page, when they find a web page that is poorly displaying Hebrew. I learned a great deal in the process and so I also made a page for web designer/coders <a href="http://aharon.varady.net/browser-test/how-to.html">to learn the correct way</a> to craft a web page that will correctly display Hebrew.

<a href="http://aharon.varady.net/browser-test/"><img src="http://aharon.varady.net/omphalos/wp-content/uploads/2012/01/banner.png" alt="" title="Web Browser Testing for Unicode Hebrew and CSS @font-face in HTML and SVG" width="932" height="131" class="aligncenter size-full wp-image-1038" /></a>

<hr />

This post was originally posted to <a href="http://aharon.varady.net/omphalos/2012/01/testing-web-browsers-as-platforms-for-hebrew-text-publishing">Aharon's Omphalos</a>.
</body>
</html>