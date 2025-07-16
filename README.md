# OPUS 4

OPUS 4 is an open source software under the GNU General Public Licence for the operation of intitutional document
servers and repositories. OPUS is an acronym for **O**nline **P**ublikationsverbund **U**niversity **S**tuttgart.
It was originally developed at the university library in Stuttgart at the end of the 90's. OPUS 4 is a complete
redevelopment that was created as part of a DFG ([Deutsche Forschungsgemeinschaft][DFG]) project between 2008 and 2010.
Since then the development has been continued at KOBV ([Kooperativer Bibliotheksverbund Berlin-Brandenburg][KOBV])
mostly.

## OPUS 4

The current version of OPUS 4 is __4.8.0.16__. It is available on the [master][MASTER] branch and compatible with 
PHP 7.1 to 8.1. 

[Documentation][DOC]
: Information on setting up a repository, for users and administrators.

[Developers][DEVDOC]
: Information for developers.

_We are in the process to moving the developer information into the GitHub Wiki. Specific information about OPUS 4
packages, might be found in the Wiki pages of their repositories._

## Testing OPUS 4

You can run OPUS 4 using Vagrant. This makes it easy to create a VM running OPUS 4 for testing or even development.
More information in the Wiki:

https://github.com/OPUS4/application/wiki/Vagrant

## Questions & Issues

Questions should be asked through the [OPUS 4 mailing list][OPUSTESTER]. We are sending out release announcements and 
other information using the mailing list. 

Bugs and suggestions can be communicated to the development team as [issues][ISSUES] here on GitHub. For suggestions
of new features or changes in OPUS 4 it is important to communicate the reason from a user perspective, the use case. 
We need to understand *why* to make the best decision for *how* to implement something new.  

If you have made modifications to OPUS 4 that could be useful for the entire community feel free to submit a [pull
request][PULLREQUESTS]. We won't always be able to respond immediately, but we will take a look at your changes. 
It is important to communicate the idea behind a modification.    

## More Information

Currently, we are using an internal Jira-System at KOBV to manage the tasks for the OPUS 4 development. We are starting
to use GitHub more and more to communicate the development goals and progress. 
At the moment, the [milestones][MILESTONES] here on GitHub mostly reflect larger technical debts that need to be fixed
in order to continue to expand the functionality of OPUS 4 in the future. The [projects][PROJECTS] are meant to show
currently ongoing efforts. However, we are still experimenting and trying to figure out how to use the GitHub features
in the best possible way without it taking too much time away from the developing work.

[OPUS4]: https://www.kobv.de/entwicklung/software/opus-4/
[DEVDOC]: https://www.opus-repository.org
[DOC]: https://www.opus-repository.org/userdoc
[KOBV]: https://www.kobv.de
[DFG]: http://www.dfg.de
[OPUSTESTER]: http://listserv.zib.de/mailman/listinfo/kobv-opus-tester/
[ISSUES]: http://github.com/OPUS4/application/issues
[MASTER]: https://github.com/OPUS4/application/tree/master
[PULLREQUESTS]: https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/about-pull-requests
[MILESTONES]: https://github.com/OPUS4/application/milestones
[PROJECTS]: https://github.com/OPUS4/application/projects
