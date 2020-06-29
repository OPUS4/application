# OPUS 4

OPUS 4 is an open source software under the GNU General Public Licence for the operation of intitutional document
servers and repositories. OPUS is an acronym for **O**nline **P**ublikationsverbund **U**niversity **S**tuttgart.
It was originally developed at the university library in Stuttgart at the end of the 90's. OPUS 4 is a complete
redevelopment that was created as part of a DFG ([Deutsche Forschungsgemeinschaft][DFG]) project between 2008 and 2010.
Since then the development has been continued at KOBV ([Kooperativer Bibliotheksverbund Berlin-Brandenburg][KOBV])
mostly.

## OPUS 4.7-RC (Release Candidate)

The release candidate should be used to test updating OPUS 4 instances and giving feedback to the developers 
[here][ISSUES] or using the OPUS 4 [tester mailing list][OPUSTESTER]. Thank you very much!

The release candidate is available on the [4.7-RC][BRANCH47RC] branch.

Depending on the level of customization of your instance the update might require some work. A lot of files were 
changed and the list of new features and modifications is long.   

We are still working on a number of issues before we can release the final version of OPUS 4.7. Here some of the 
most important points.

- Configuration of search facettes for enrichments 
- Translation management in Setup area of administration
- Update issues (?)
- Documentation is outdated and needs to be updated (especially customizing translations)

## Current Version - OPUS 4.6.3

The current version of OPUS 4 is 4.6.3. It is available on the [master][MASTER] branch. 

[Documentation][DOC]
: Information on setting up a repository, for users and administrators.

[Developers][DEVDOC]
: Information for developers.

## OPUS 4 at GitHub

In 2015 the development was moved to GitHub in order to better support collaboration in the continued development
efforts. The first OPUS 4 version developed at GitHub is 4.5. Starting with this version OPUS 4 should be installed
using Git, since this will make updates for bug fixes and new features easier. More information can be found online.

## Previous (non-GitHub) Version:

The last non-Git release of OPUS 4 is Version 4.4.5 and can be [downloaded][OPUS445] from the
[OPUS 4 Homepage][OPUS4] as a tarball. The [documentation][OPUS445DOC] for this version can
be found there as well. This version is no longer supported. 

Version 4.4.5 can be installed without Git. It is recommended to use the GitHub version of
OPUS 4 for setting up new repositories. However if you are looking at migrating an existing OPUS 3
repository to OPUS 4 using this version would currently be a necessary step since there is at the moment
no working migration script for the Git version.

[OPUS4]: https://opus4.kobv.de
[DEVDOC]: https://opus4.github.io/
[DOC]: https://opus4.github.io/userdoc
[KOBV]: https://www.kobv.de
[DFG]: http://www.dfg.de
[OPUS445]: https://www.kobv.de/entwicklung/software/opus-4/download/
[OPUS445DOC]: https://www.kobv.de/entwicklung/software/opus-4/dokumentation/
[OPUSTESTER]: http://listserv.zib.de/mailman/listinfo/kobv-opus-tester/
[ISSUES]: http://github.com/OPUS4/application/issues
[BRANCH47RC]: https://github.com/OPUS4/application/tree/4.7-RC
[MASTER]: https://github.com/OPUS4/application/tree/master