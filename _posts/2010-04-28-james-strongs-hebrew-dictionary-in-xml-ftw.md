---
ID: 503
post_title: >
  Testing Our Transliteration Engine with
  help from James Strong’s Biblical
  Hebrew Dictionary
author: the Hierophant
post_excerpt: ""
layout: post
permalink: >
  http://opensiddur.org/concerning/the-open-siddur-project/development/james-strongs-hebrew-dictionary-in-xml-ftw/
published: true
post_date: 2010-04-28 18:53:08
---
<a href="http://en.wikipedia.org/wiki/James_Strong_%28theologian%29"><img class="alignright size-full wp-image-539" title="James Strong" src="http://opensiddur.org/wp-content/uploads/2010/04/220px-James_Strong_theologian_-_Brady-Handy.jpg" alt="" width="220" height="459" /></a>The mark of a particularly valuable dictionary is how long it is still  being used years after it's introduced. Marcus Jastrow's <em>Dictionary of the Targumim, Talmud Babli, Talmud Yerushalmi and Midrashic  Literature</em> (1903), Brown-Driver-Brigg's <em>Hebrew and English  Lexicon of the Old Testament</em> (1906), and James Strong's <em>Concise Dictionary of the Words in the Hebrew Bible with their Renderings</em> (1890) are all standard reference works still used today.

But dictionaries are not only invaluable reference tools for scholarly research. They are also useful features in online applications--and not only for their definitions. Dictionaries also include transliterations. Both translations and transliterations are features we would like to provide for users of the Open Siddur application we are developing. But in order to provide these features, the dictionaries must be digitized and their contents encoded in a standard searchable format.

Strong's dictionary, prepared as a companion to his famous concordance, contains a complete list of Hebrew words that appear in the TaNaKh, transliterated with a consistent ruleset.  By formatting the words in the dictionary and replicating the transliteration as it appears in the dictionary the Open Siddur Project could test the transliteration engine that will be used to transliterate Hebrew text with <em>nikkud</em> (vowels) to any other script (Latin, Cyrillic, Arabic, Amharic, etc.).

Strong's dictionary was digitized (<a class="pdf" href="http://www.heraldmag.org/olb/Contents/dictionaries/SHebrew.pdf" target="_blank">PDF</a>) in 1998 by unknown contributors. Converting the digitized, machine-readable text to an open standard format was a milestone sought by a number of projects including <a href="http://openscriptures.org/" target="_blank">OpenScriptures</a>, an open source project digitizing and encoding variant manuscripts of the Gospels. In partnership with Open Scripture contributing developers, the Open Siddur Project created a quality XML encoding of Strong's dictionary. Work began early in February and was completed by the second week of April. The data and XML is available as public domain text, <a href="http://github.com/openscriptures/strongs/downloads" target="_blank">here</a>.

The project served to test our transliteration engine and develop a good working and ecumenical relationship between two worthy open source projects sharing technology for advancing the digital humanities. The three main contributors on the project were David Troidl, Ze'ev Clementson, and Efraim Feinstein (lead developer of the Open Siddur). Ze'ev initiated the project in a forum discussion, <a href="http://groups.google.com/group/opensiddur-talk/browse_frm/thread/6c323b6a32196432/32c95bdc01a6c020" target="_blank">here</a>.  (Ze'ev is a regular contributor to the Open Siddur Project and creator  of innovative <a href="http://beresheit.blogspot.com/2010/04/hebrewbible-version-40-with-ipad.html" target="_blank">Jewish educational software</a> for the iPhone/iPad  platforms (so far)). Initially, David obtained Strong's Hebrew data in the form of a PHP script from <a href="http://www.tyndalearchive.com/Brewer/author.htm" target="_blank">Dr. David Instone-Brewer</a>. (The data is the basis for Instone-Brewer's website <a href="http://www.2letterlookup.com/" target="_blank">2 Letter Lookup</a>).  Troidl then parsed out the data and converted it to the Open Scripture Information Standard (<a href="http://en.wikipedia.org/wiki/Open_Scripture_Information_Standard" target="_blank">OSIS</a>) XML schema, "using the best available OSIS structure for the data, since OSIS has no official dictionary module," he explained. <del datetime="2010-04-28T23:52:31+00:00">Efraim </del> Ze'ev converted the XML to the Text Encoding Initiative (<a href="http://en.wikipedia.org/wiki/Text_Encoding_Initiative" target="_blank">TEI</a>) standard used by the Open Siddur Project. Efraim helped define the tag usage for the Open Siddur extension to the TEI -- the <a href="https://github.com/opensiddur/opensiddur/wiki/JLPTEI-101:-00:-Introduction" target="_blank">JLPTEI</a>.

James Strong is best known for his concordance and the scholarly tools  he innovated show a prescient interest in linked data. Were Strong to  look down from his perch in the heavenly yeshiva/academy at our work, I think he might be quite pleased with this collaboration. I asked David, Ze'ev and Efraim if they would comment on their work together contributing to the Open Siddur Project.

Why was updating Strong's Hebrew Dictionary to Unicode and XML such an important target?

<blockquote>David: The existing ASCII transliterations were neither  fully accurate, nor a faithful representation of the ones in Strong's  printed dictionary.

Ze'ev: Having a standard "dictionary" in Open Siddur allows us to  provide new functionality that we didn't have before. So, for example,  someone might want to make a child's siddur or a siddur for people who  don't know understand or read Hebrew well that contains definitions  (perhaps at the bottom of the page or in the margin) for some of the  less common Hebrew words. We now have the ability to add accurate  transliterations (either alongside the Hebrew or instead of the Hebrew  in the siddur) for people who can't read Hebrew.

Efraim: It opened a route of collaboration  between  projects. Specific to the Open Siddur, working on this   provided a specification  for a new type of associated  data --  dictionary  words.</blockquote>

How did this collaboration advance your project's specific goals?

<blockquote>Efraim: By having a second independent implementation of transliteration  we could debug our transliterator and discover corrections to make in  Strong's original document.

Ze'ev: The collaboration with David was great in that it helped us to   identify a number of shortfalls/bugs in the existing Open Siddur   transliteration code. However, the collaboration worked both ways in   that, as a result of the email discussions, David was able to make   changes to his source document (e.g. - adding qamets qatan, holam haser   for vav, spelling corrections, etc) and Open Siddur was able to   "robustify" the transliteration code so that it handled more "corner   cases" than it did before (e.g. - adding dagesh transliteration support   for non-bgdkft letters, improved handling of silent letters, support  for  literally-transliterated tetragrammaton, etc) and it also helped us  to  generally test/verify other transliterations. We also now have a   "transliteration tester" which lets us automatically validate our   programmatically-generated transliterations against static versions of   the Strong's transliterations (this can be used in unit tests in the   future to ensure that transliteration code changes don't "break" the   existing Strong's transliteration logic).

David: Ze'ev's questions about Strong's transliterations, while   developing his transliteration table, spurred me on to work on a project   that I had been wanting to do for some time anyway.  After using the   existing ASCII transliterations as a comparison metric, we started   trading our results, to compare the Unicode transliterations.  This   allowed both of us to fine tune our code and produced a significantly   higher degree of accuracy in the transliterations themselves.</blockquote>

The XML and code generated by this effort are available to everyone with open/free culture licensing. Besides this obvious advantage, how will this work help other folk's projects worldwide?

<blockquote>David: Many Internet resources that I've come across fall far short of a print publisher's tolerance for error.  While the availability of the information is commendable, I would like to see a greater emphasis on the accuracy of existing resources.  There are many texts, in print and electronic, that utilize Strong's numbering system, and a reliable reference will be a benefit.

Ze'ev: The major additional "public" benefit from the exercise is that there is now an open-source, digital Strong's source document that has accurate transliterations in the style defined in the Strong's Concordance book and which has been edited to correct misspellings (both Hebrew misspellings and errors in the English transliterations). Already, at least one application (my Hebrew Bible iPhone/iPad app) is utilizing the Strong's Concordance data and currently provides <a href="http://sites.google.com/site/hebrewsoftware/_/rsrc/1270146166151/images/ipad2.jpg" target="_blank">integrated word definition display</a> when reading the TaNaKh  as well as <a href="http://sites.google.com/site/hebrewsoftware/_/rsrc/1260640562866/images/HebrewBible3.jpg" target="_blank">Hebrew root lookup</a>.</blockquote>

What new opportunities for collaboration and development do you see coming out of this work?

<blockquote>Efraim: One thing we (Open Siddur) don't have is a way for the general public to get access to it!  It's in <a href="http://code.google.com/p/jewishliturgy/source/checkout" target="_blank">SVN</a>, and we have a way to convert it to HTML, but no public interface [exists yet] to transform it or to look up words.

David: The ideal markup format is still an open question.  My version is  pushing the limits of OSIS, in its present form.  The TEI form that  Ze'ev [Open Siddur] is using is verbose and repetitive.  I have some ideas that are  still coalescing.  Also, we are moving toward adding a Brown, Driver,  Briggs layer on top of the Strong's data, striving for a richer and more  accurate dictionary.

Ze'ev: This was a bit of a unique scenario in that we were both  attempting to validate independently-developed transliteration  code with the same source document. We have now completed the  validation of the Strong's transliteration scheme and have  discussed following through with a similar exercise for the SBL  transliterations.</blockquote>