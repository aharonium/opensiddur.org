---
ID: 33
post_title: Project History
post_name: history
author: the Hierophant
post_date: 2009-06-25 06:12:47
layout: page
link: >
  http://opensiddur.org/about-this-project/development/history/
published: true
tags: [ ]
categories:
  - Meta
---
<div class="english">
The Open Siddur was first conceived in 2001-2002 by Aharon Varady as an open source project archiving the sections of the Siddur, tagged according to the era of their composition and allowing user edits and user-contributions via a PERL/MySQL backend. In <a href="http://aharon.varady.net/omphalos/2002/08/update-2002-08-08">August 2002</a>, he penned some of his earliest thoughts on the project:

<blockquote>I'm working on a proposal for a project that I'm calling the "Open Siddur". The goal of the project is to bring back the creative power of t'fillah to the individual while encouraging the feeling of solidarity with and awareness of the larger Jewish community and their diversity. The draft proposal is located <a href="http://aharon.varady.net/open_siddur/index-old.htm">here</a>. Growing up Jewish and "Orthodox", I often heard that regardless of one's personal gripes with this or that aspect of the tradition, it was religiously dishonest, to "pick and choose" elements from the tradition one desired to practice from those that were odious. Later in college, I heard as a tenet ascribed to the "post-modern", that the past and culture can be plundered for inspiration and re-contextualization. This other "picking and choosing" seemed to me that, while fun, was also disrespectful and cultural appropriation, similar to how the Kabbalah was re-contextualized within medieval Christendom. But now I'm thinking that for a Judaism which is under attack by mono-culture fundamentalists, deep understanding and appreciation of Jewish worldviews can be accomplished by individuals studying and selecting and even creating new elements for their personal T'fillah (one of the last spheres where the individual still retains some creative power within the constraints of Jewish traditional discipline. The 1960s saw a movement for Jewish chavurah movements to explore creativity in Judaism as a shared experience. But what is needed, and has sorely been lacking for as long as I can see is *<strong>help</strong>* from the Siddur and the Jewish tradition itself to encourage and inspire an *<strong>individual's</strong>* creativity. After all, the tradition is relying on the individual's energy to maintain itself to the next generation. This is usually accomplished through a religio-ethnic sense of duty and self-righteous discipline, which yield its own simple rewards to the individual. If we are to conform for the sake of a unity in tradition, we should be aware not only of the purpose of our conformities, but also the limits of our selfless conforming. The religion must maintain and encourage space and freedom for individuality and creativity, and not limit individuality in spirituality and individual expressions to fables about revered rabbis.</blockquote>


<h3>Antecedents</h3>

The idea for an Open Siddur was in part inspired by Rabbi Jacob Freedman's <em>Polychrome Historical Haggadah</em> (1974). Rabbi Freedman's innovation was to clearly represent the text of the Haggadah and Siddur as aggregate texts with different authors and periods of composition. (See Freedman's color coded legend included on a bookmark distributed with his Haggadah. In a <a href="http://opensiddur.org/wp-content/uploads/2009/12/Jacob_Freedman_-_Polychrome_Historical_Prayerbook.pdf">pamphlet</a> illustrating his vision for a Polychrome Historical Jewish Prayerbook, Rabbi Freedman wrote:

<blockquote>This is perhaps the first attempt to present for publication a polychrome historical prayerbook. The author herewith presents a random selection of prayers in colors merely as examples to show the various levels of historical development....The marginal symbols, also in color, indicate the period when certain prayers or phrases were first formulated and/ or introduced into the prayerbook. The references are not to be considered exhaustive.
[gallery type="rectangular" link="file" ids="9978,9979"]

</blockquote>

Freedman passed away in 1986 before he could complete negotiations for the publication of his prayerbook, and tragically, his manuscript is now lost. Considering that Freedman's use of color amounted to an early application of metadata to text, it seemed clear that a much more nuanced approach to the origin and inclusion of text in the Siddur could be supported with open source database technologies.

A couple years earlier, Reb Zalman Schachter-Shalomi described a shared digital database for new and old liturgy in an article for the Havurah newsletter, entitled "<a href="http://opensiddur.org/development/press/database-davvenen-by-reb-zalman-schachter-shalomi-havurah/">Database Davennen</a>" (ca. 1984): 

<blockquote>The idea is to have a central database, into which lots of people in the havurah movement can make their contribution. Many options could be included for all parts of the service, created by different people and different groups. People will add their own personal rubrics, based on their own experience. Right now we have calendar rubrics, e.g., on Monday and Thursday you don’t recite this, but we will add, “Birchot haShachar works best close to dawn,” and ways to involve the group. We don’t put it as law, but as suggestion, as a recipe. We make a big appendix to the siddur. We leave out some of the things now in the siddurs, things that are so hackneyed that you can’t put them in your mouth, but we include the ones that are good. And we might want to have a series of poems. Many poets who never think of themselves as religious poets (or even religious people), are writing poetry that is current prophesy, that is modern piyyut. We want to use that stuff for musaf, where we need one more fortissimo after k’riat ha Torah. This poetry is not the kind of thing that’s made for reading by the group, but if someone reads it well and dramatically, the congregation can say amen afterward.</blockquote>

The only thing missing from Reb Zalman's idea was a licensing framework by which all participants could contribute and draw from each others work under current Copyright law. But even basic technical hurdles remained into the 2000s. The idea of an Open Siddur remained a pipe dream due to the lack of available, copyright-accessible, digitized liturgy, as well as the lack of mature technology to automate transcription of Public Domain source material (i.e., Hebrew OCR tools that recognize nikkudot and other diacritical marks).

<h3>Inspirations</h3>

What inspired the Open Siddur Project? Several related ideas. First, there is the essay "<a href=" http://youtu.be/c0v2apGezu0">Immediatism</a>" by Peter Lamborn Wilson, which articulates how alienation is expressed through media (we might expect or desire prayer, of all media, to be less mediated than other media, say Television or PowerPoint presentations). 

[youtube http://youtu.be/c0v2apGezu0]

<a href="http://opensiddur.org/wp-content/uploads/2009/06/Neal-Stephenson-Diamond-Age-cover.jpg"><img src="http://opensiddur.org/wp-content/uploads/2009/06/Neal-Stephenson-Diamond-Age-cover.jpg" alt="Neal Stephenson - Diamond Age (cover)" width="212" height="314" class="alignright size-full wp-image-10557" /></a> Second, there is the do-it-yourself ethos and the celebration of studied craft traditions that derives directly from the Arts and Crafts Movement of William Morris and his Kelmscott Press. This idea is described brilliantly in the life and work of bespoke artisans and master book artists in Neal Stephenson's 1995 sci-fi novel, <em>The Diamond Age (or a Young Lady's Illustrated Primer)</em>. 

We've already, above, discussed the illustration of textual metadata in Rabbi Jacob Freedman's unpublished Siddur Bays Yosef (Polychrome Historical Prayer Book). The need for a free-culture movement, first articulated by Richard Stallman concerning software, and later by Lawrence Lessig concerning all manner of cultural products, is central to this effort in reviving creativity and artistic mastery in works of Judaism. Finally, Aharon Varady's first-hand experience witnessing the benefit of radical Jewish pluralism in the grassroots intentional community, Jews in the Woods, convinced him of the value of sharing inspirations and knowledge throughout the Jewish community regardless of sectarian or denominational affiliation.


<h3>Open Source, Open Content, and Open Standards</h3>

In the 2000s, the potential of Torah databases and open source licensed user-generated content projects was just beginning to be explored. Digitized liturgy began to appear online at sites like Hebrew Wikisource and Daat.co.il, however, on the whole, these new digital editions were provided without attribution, and thus lacked the authority inherent in knowing the clear provenance of a textual witness. Inspired by Douglas Rushkoff's call for an Open Source Judaism, Dan Sieradski developed a haggadah he called the Open Source Haggadah (2005) and proceeded to work on a web application he called the Open Source Siddur. However, these two projects lacked a clear Open Content license or attribution information for contributed content. Development of Dan's siddur project stalled in 2006. As with Dan's siddur, a number of other online siddurim cropped up on sites providing digitized liturgical content in a small variety of popular nusḥaot. 

Meanwhile, a graduate student at Harvard, Efraim Feinstein, was looking for an active Free and Open Source (FOSS) project for developing a siddur. A self-taught hacker, Feinstein found that the other existing projects were either technologically inadequate to the task, or insufficiently supportive of free culture values.  While Varady's "Open Siddur Project" proposed a similar idea, a six year old web page without a code base made him think that the author had gone on to other things and that the project was forgotten.   Finding no active and worthwhile project to partner with, Efraim began on his own, calling it the <em>Jewish Liturgy Project</em> (JLP) because "all the obvious names were taken".

Feinstein coded on and off, going through different versions of standard and non-standard methods of XML encoding, and finally settling on an encoding schema defined by the <a href="http://www.tei-c.org">Text Encoding Initiative</a> (TEI). Efraim began transcribing a haggadah over a period of two months, from January to March 2008.  Not wanting to announce the project until he had produced something minimal to release, the project was not made public until the first code was committed to <a href="http://github.com/opensiddur">Google Code</a> on December 10, 2008.

Later that month, on December 31, 2008, a young self-taught Brooklyn hacker named <a href="http://realazthat.blogspot.com">Azriel Fasten</a> contacted both Feinstein and Varady, asking the latter about reviving the Open Siddur. Varady and Feinstein were soon introduced and agreed on a common vision for the project. Feinstein, who fully embraces the culture of free and open source software development, welcomed Varady's willingness to contribute, and was happy to connect his project to that of the Open Siddur name. Varady was overjoyed that a passionate developer community was coalescing around this newly shared vision and was eager to help Feinstein lay the foundations for a robust server process, XML encoded digital text archive, and web application.

<h3>Public Launch, 2009</h3>

Aharon Varady submitted a proposal to attend the PresenTense Institute in Jerusalem's summer workshop. His participation was supported through the Kaplan Family Foundation and a grant from the Jewish Publication Society. The Open Siddur Project was publicly launched with the help of PresenTense Institute and an article in Ha'aretz by Raphael Ahren. A fiscal sponsorship through an established non-profit was arranged with Bob Goldfarb's Center for Jewish Culture and Creativity.

While Aharon worked to develop the website, cultivate an online community, and solicit resources (including grant funding), Efraim set work on developing the Open Siddur database, server, and client software. By 2010, it was clear that development of the software would take a number of years, and that funding would not be forthcoming without a working application to show to prospective funders. That year, the project website at opensiddur.org was transformed into a showcase of liturgy and related work shared with Open Content licensing. 

Initially, development of the Open Siddur focused primarily on building core processes, standards, and text resources. By 2013-2014, much of the heavy lifting on this work was completed. Work on the client interface, began in earnest in 2014.

<h3>Current Status</h3>

With modest financial needs and support from numerous small donors, Aharon and Efraim continue to work on the project. Evangelism for <a href="http://en.wikipedia.org/wiki/Open_Source_Judaism">Open Source in Judaism</a> remains a core value for the project and Aharon and Efraim have presented at numerous gatherings including the Foundation of Jewish Non-Profits, the Academy for Jewish Religion, Limmud NY, NewCAJE, and the Minerva/EVA Conference on Digitization of Jewish Culture.

In general, the project lacks capable volunteers: software engineers, user interface developers. We are in dire need of help developing our <a href="https://github.com/opensiddur/opensiddur/wiki/Open-Siddur-Client-Interface-Outline-(draft)">front-end user interface</a> to take advantage of our back-end sever architecture. We believe that a workable and attractive user interface will help drive increased volunteer attention from transcribers and translators to our project.

For more detail, please check out our list of <a href="https://github.com/opensiddur/opensiddur/milestones">Milestones</a>. Discussion of the Open Siddur continues on our <a href="http://groups.google.com/group/opensiddur-tech/">technical discussion list</a> and on our <a href="https://www.facebook.com/groups/opensiddur">popular discussion group on Facebook</a>. (Announcements are made primarily through <a href="http://twitter.com/opensiddur">Twitter</a> and our <a href="https://www.facebook.com/opensiddur">Facebook page</a>.) Project development continues on our code base hosted at <a href="https://github.com/opensiddur/opensiddur">Github</a>. Transcription of texts and project documentation is available though our <a href="http://web.archive.org/web/20100507124255/http://wiki.jewishliturgy.org:80/Conditionals">conditional  inclusion feature</a>.

Our community is open, so please count yourself as invited. We welcome <a href="http://opensiddur.org/contribute/">your contribution</a>. If you're a coder, please introduce yourself and your capabilities on our <a href="http://groups.google.com/group/opensiddur-tech/">discussion list</a>.

Media inquiries can be directed to our Hierophant, Aharon Varady, via our <a href="http://opensiddur.org/contact/">contact page</a>.
</div>