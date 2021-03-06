<html>
<head></head>
<body>
Title: Why all the software? by Efraim Feinstein<br />
Primary contributor: Efraim<br />
For attribution and license, please consult the following URL: <a href="http://opensiddur.org/?p=221">http://opensiddur.org/?p=221</a>
<p />
<hr />

<div class="english">
One question I've been asked a number of times about the Open Siddur Project is: why are you developing all that software?  It's a fair question.  After all, the siddur is just text.  There are other do-it-yourself siddur kits out there.  They sell you (or, more accurately, license you) a text.  You open the text in a word processor, make a few stylistic changes, and voila, you have your own custom siddur.  The "advanced" ones may even hand you one copy of a "nusaḥ Ashkenaz" siddur, one copy of a "nusaḥ Sefard" siddur, and one copy of a "nusaḥ Edot Hamizrah" siddur, giving you some choices.  All good, right?  So, once again, why does the Open Siddur need so much software?

In actuality, there is no such thing as a single text called <em>the</em> Ashkenazic siddur, <em>the</em> Hasidic siddur, or <em>the</em> Sephardic siddur, etc.  A nusaḥ is a major division which uniquely specifies a common denominator of customs within a group of customs.  Within Ashkenaz, there are differences between the Polish and German customs.  The Iraqi custom is not the same as the Yemenite custom, and the Lubavitch custom is not the same as the Breslov custom.  There are also divisions within each rite along major the philosophical boundaries that have developed in recent centuries, which lead to differences in custom and text.  The traditional-egalitarian rite (usually a variant of the Ashkenazic rite), for example, is still undergoing major evolution.

Now, let's say that we provided a single text for each sub-rite within the major rites, and simply copy-pasted the common text between them.  The amount of replication of very similar material between the texts would be huge.  If a mistake were found in one copy, it would have to be corrected separately in all copies.  Further, there would be no real connection between the copies other than their content.  If we were tracking some data (say, grammatical or historical data about the text), it too would have to have each change copied to all copies of the texts.  This would quickly result in an unmaintainable mess.

The Open Siddur's current approach is different.  It involves (1) minimizing the amount of stored text, (2) storing the differences between the texts and (3) sets of conditions that specify when each variant is selected.  If a typo is corrected in one variant, it is corrected for all variants.  Any stored metadata is also automatically consistent between all texts.  An additional advantage of this approach is that a community with a custom that differs from the "base" custom of the rite only has to make a different choice of variants.  No change is required in the text in order to support a slightly differing custom.

The disadvantage of this approach is that each user likely only wants one variant of the text.  Something has to (1) convert human-typed text into the unified format used in the archive and (2) splice the archival text into a unified "printable" text that can be used as a siddur.  And that is one answer to why we need to develop software.
</div>
</body>
</html>