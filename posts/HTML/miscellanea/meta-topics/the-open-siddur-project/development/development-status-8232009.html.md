<html>
<head></head>
<body>
Title: Development Status (2009-08-23)<br />
Primary contributor: hierophant<br />
For attribution and license, please consult the following URL: <a href="http://opensiddur.org/?p=179">http://opensiddur.org/?p=179</a>
<p />
<hr />

<strong>Open Siddur Project Development Status</strong> <strong>as of 8/23/2009</strong>

<strong> </strong>This is our first development status post. Normally, this post will try to wrap up what we've achieved in the past week. Since this is our first, I'll be summing up some of the progress we've made in the last month or so. It will serve as something like a newsletter, and will be posted on the discussion list and at <a href="../">opensiddur.org</a>. <a href="https://opensiddur.org/contact/" target="_self" rel="noopener noreferrer">Contact us</a> if you want to include something we haven't covered.

If you'd like to get news of Open Siddur as it happens, make sure to follow <a href="http://twitter.com/opensiddur">@opensiddur</a> at Twitter.

<strong>Contributions</strong> (Aharon)

<div style="margin-left: 40px;"><span style="text-decoration: underline;">Digitized</span>:  <strong>Reb Zalman Schachter-Shalomi contributed his Weekday Siddur under a CC-BY-SA license.</strong> Anyone who would similarly like to contribute their texts to the Open Siddur under CC-BY-SA may now do so by simply emailing us with the following statement attached.</div>

<blockquote style="border-left: 1px solid #cccccc; margin: 0pt 0pt 0pt 6.8ex; padding-left: 1ex;">"I am/We are the original author(s) of _______ and I am/We are licensing the following attachments under the <span>Creative</span> <span>Commons</span> Attribution-ShareAlike 3.0 Unported License.  Attribution may be given as 'Contributors to the Jewish Liturgy Project/Open Siddur', with the author's name(s) _______ included in the contributors list."</blockquote>

<div style="margin-left: 40px;">(You may also contribute your work under CC-BY, or CC0; simply change the name of the license in the above. Only CC-BY-SA and CC-BY *need* the attribution line.)

Efraim reports that all the texts of the TaNaKh have been reformatted for use with our new schema.

<span style="text-decoration: underline;">Undigitized</span> scanned texts: Aharon discovered some good scans of historical siddurim in the public domain at the Internet Archive, scanned and contributed by the University of Toronto, including the Seder Rav Amram hashalem (1922). Next step: deconstruct PDF into constituent image files according to the JLP/OS scanning guidelines (http://wiki.jewishliturgy.org/Transcription:Scanning_Guidelines).

Wikisource siddurim (<span><a title="קטגוריה:סידורי תפילה" href="http://he.wikisource.org/wiki/%D7%A7%D7%98%D7%92%D7%95%D7%A8%D7%99%D7%94:%D7%A1%D7%99%D7%93%D7%95%D7%A8%D7%99_%D7%AA%D7%A4%D7%99%D7%9C%D7%94">קטגוריה:סידורי תפילה</a></span>) that used to be licensed GFDL are now licensed CC-BY-SA, making their license compatible with Open Siddur. For those siddurim at wikisource that are truly in the Public Domain we need to scan the original sources and begin proofreading the transcription, e.g. <a title="סידור תורה אור" href="http://he.wikisource.org/wiki/%D7%A1%D7%99%D7%93%D7%95%D7%A8_%D7%AA%D7%95%D7%A8%D7%94_%D7%90%D7%95%D7%A8">סידור תורה אור</a>. <strong>We need help finding a scan or Public Domain (pre-1923) print copy of Siddur Torah Ohr by R. Schneur Zalman of Liadi.</strong>

<span style="text-decoration: underline;">Scanning</span>: Yonah and yitz_ (the latter, who we met on our 8/16 live chat) offered to investigate scanning protocols at UT. Ilan offered to do the same for Cornell. We need to review scanning guidelines.

<span style="text-decoration: underline;">Automated Transcription</span> (via OCR): Aharon found an open source Hebrew OCR that recognizes nikkud and teamim. Efraim says if we can get 80% accuracy minimum we're in business -- we'd prefer 95% accuracy (1 mistake every 20 characters). We need testers. Also, if you are a coder interested in OCR, both Hocr and qHocr (a cross platform Qt4 port) would love your assistance.

<span style="text-decoration: underline;">Manual Transcription</span>: 11 pages of Seder Avodat Yisroel transcribed and ready for proofreading, 470 more to complete (sans commentary).</div>

<strong>
Software Development</strong>

<div style="margin-left: 40px;"><span style="text-decoration: underline;">Frontend</span> (Azriel) -- Transcription Interface: Old interface still running in wiki. New interface is ready except for the following issues... (needs help?). Other interfaces: XML endoding Interface, Translation, Commentary, and the other collaborative and personal user interfaces that will make up the Open Siddur web application will use the XML database. Looking at the new transcription interface as a proof of concept API for future interfaces. <strong>Azriel wants to get some other volunteer developers comfortable with using Google Web Toolkit </strong>(GWT, <a href="http://code.google.com/webtoolkit/">http://code.google.com/webtoolkit/</a>) and get familiar with his code.

<span style="text-decoration: underline;">Backend</span> (Efraim) -- New XML database using eXist is running. Azriel using it for transcription interface.
Toolkit API: contributor list management API and bibliography managment API source code is now available.   Some of this code is being rewritten to extract all eXist-specific features and syntax into separate XQuery files. Also, working on XML validation (RelaxNG, Schematron) for use during real-time updates.  <strong>Rendering code requires a complete rewrite for the new JLPTEI encoding.</strong></div>

<strong>Documentation</strong>

<div style="margin-left: 40px;">Draft of the Website User Interface needs to be revisited. <a href="http://web.archive.org/web/20090918153554/http://wiki.jewishliturgy.org:80/Website_User_Interface">http://wiki.jewishliturgy.org/Website_User_Interface</a> . David Cohen recommends we write some use case scenarios in addition to our diagramming the architecture of the front/back ends.

Here's a document on the wiki that is very useful: <a href="https://opensiddur.org/copyright-policy/">http://wiki.jewishliturgy.org/JewishLiturgyProject:Copyrights</a>

The first draft of the JLPTEI guidelines are written on the wiki. Needs review, requesting feedback.  Needs work on an encoding tutorial, with the intended audience of application developers. <a href="https://github.com/opensiddur/opensiddur/wiki/JLPTEI-101:-00:-Introduction">http://wiki.jewishliturgy.org/JLPTEI</a>

A working draft of the schema ODD which compiles to RelaxNG, DTD, and W3C XML Schema) is available in our Google Code-hosted Subversion archive. (ODD stands for <span dir="ltr">One Document Does it all, a</span>
<div>TEI schema, see http://jewishliturgy.googlecode.com/s<a href="https://github.com/opensiddur/opensiddur/wiki/Intro-to-hacking-the-schema">vn/trunk/schema</a> . More info on ODD at <a href="http://www.tei-c.org/Guidelines/Customization/odds.xml">http://www.tei-c.org/Guidelines/Customization/odds.xml</a>).</div>
</div>

<strong>System Administration</strong> (Azriel, Efraim, and Aharon):

<div style="margin-left: 40px;">We want to thank Josh Rosenberg for hosting the JLP/Open Siddur documentation wiki! Wiki is now addressed at <a href="https://mail.google.com/mail/goog_1251034984015">http://</a><a href="https://github.com/opensiddur/">wiki.jewishliturgy.org</a>. Please update your links.
Efraim purchased a virtual private server service. (Thanks Efraim!)
The new XML database is hosted at http://shell.jewishliturgy.org:8080/exist on the VPS.
New Transcription interface is not ready yet but will be accessible at <a href="https://mail.google.com/mail/goog_1251034984013">http://</a>www.jewishliturgy.org
Opensiddur.net has been refreshed, links and material in pages and posts updated.
All JLP/Open Siddur sites now being tracked with google analytics. (Other statistics also available.)</div>

<strong>Volunteer Management</strong> (Aharon)

<div style="margin-left: 40px;">50 persons have now filled out the survey form at Open Siddur.net
Began replying to volunteer transcribers. Waiting for word of new transcription interface. Interested in what feedback to provide volunteer translators, commentary writers. <strong>
Would like an XML encoding interface for digitized texts these folk may want to contribute.</strong>
A handful of people would like to donate money. Holding off on soliciting funds for now. Really looking for more in-kind contributions. Researching how to structure foundations to support open source software development (e.g., Mozilla Foundation).</div>

<strong>Organization/Structure</strong>

<div style="margin-left: 40px;">Open Siddur is not yet incorporated as a non-profit entity, however, through fiscal sponsorship provided by the Center for Jewish Culture and Creativity (<a href="http://jewishcreativity.org/">jewishcreativity.org</a>), a registered 501(3)c non-profit, <strong>Open Siddur can now receive tax-deductible donations</strong>. (Thanks Bob Goldfarb!)

Open Siddur and the Jewish Liturgy Project are the names of projects initiated by Aharon Varady and Efraim Feinstein, respectively. Efraim and Aharon are drafting a "team charter" to further define a structure and mission statement compatible with their mutual efforts as well as their shared open source and free culture values.</div>

<strong>Communication and Promotion</strong> (Aharon)

<div style="margin-left: 40px;">In three weeks, 180 members to our facebook group, 50 completed surveys at <a href="../">opensiddur.org</a>, 56 members on discussion list, 49 twitter followers. Facebook group used to communicate upcoming live chats, solicit for volunteers, update dev status, and other important news.

Efraim asks, What sorts of print materials can we create to help promote Open Siddur and attract developers and volunteers? Can we get Jewish day school and high school computer clubs to take an interest in helping develop Open Siddur and by extension learn about Open Source and Free Culture?

First live chat on August 16th and we had a minyan! Focused at first on soliciting technical help. Non-software devs also want to help. Initial response: Open Siddur needs help with historical research, scanning, and promotion. Non software devs can also help with reseach, transcription, and documentation. Translations, art, and commentaries prepared today can be contributed tomorrow. (Thanks to everyone who attended!)

Rabbi Shalom Berger at the Lookstein Foundation noted Open Siddur in their recent newsletter (8/20). (Thanks!)</div>

<strong>Team Member Updates</strong>

<div style="margin-left: 40px;">Aharon is back in the US after finishing up the PresenTense Institute Summer workshop on Jewish social entrepreneurship and innovation, and is once again working on the Open Siddur. First week in September he'll be relocating to NYC for a fellowship at Yeshivat Hadar. Besides blogging at <a href="http://aharon.varady.net/omphalos">aharon.varady.net/omphalos</a> and at <a href="../">opensiddur.org</a> he tweets at <a href="http://twitter.com/aharonium">@aharonium
</a>
Sarah Allen, a volunteer translator for Open Siddur, is stepping up as our Israel contact person. (Welcome aboard!) She tweets from @sarahballen

Azriel will need to take a step back from some Open Siddur work with the onset of school, hopes to have committed a substantial part of what will become the basis for the transcription framework. Azriel is now blogging about technical development problems/solutions/milestones at <a href="http://realazthat.blogspot.com/">realazthat.blogspot.com</a>.

In June, Efraim graduated with a doctoral degree from Harvard in biophysics. Congratulations Efraim! Efraim occasionally tweets from <a href="http://twitter.com/efraimdf">@efraimdf</a></div>

<a href="../"></a>
</body>
</html>