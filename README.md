# opensiddur.org

Every post on opensiddur.org parsed from Wordpress eXtended RSS (XML) export files.

These XML files are manually generated from our Wordpress backend. (I'd like to figure out how to automatically generate them.)

Individually parsed posts (see posts/HTML) are generated from these XML files using a <a href="https://gist.github.com/aharonium/1d148b57e2b8488f68e2f2781ce92e00">fork of ruslanosipov's wxr2txt.py script</a>. 

The generated posts with the .md markdown extension contain the HTML of the body of each post on opensiddur.org as well as some limited metadata. Not all postmeta data (e.g., co-authors, categories, tags, and license information) is getting parsed into these posts right now. Figuring out how to do this in python or via XSLT is a current goal.

Content in the posts file is shared under a mix of Creative Commons Attribution-ShareAlike and Creative Commons Attribution licenses. Inspect the postmeta "open_content_license" metakey, to determine the specific license for any specific post.

Content in all other files (pages, authors, media) is shared under a <a href="https://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International</a> license.

Email addresses of users and commenters have been redacted.
