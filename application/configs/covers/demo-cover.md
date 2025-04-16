---
# 
# Demo Markdown template for PDF cover generation
# -----------------------------------------------
#   
# This template can be used by OPUS4 to dynamically render a PDF cover which can be prepended
# to a PDF file on download.
# 
# REQUIREMENTS:
# - This template requires the packages `pandoc` (>= v2.11) and `texlive-xetex` to be installed.
#   
# - If not commented out (see `sansfont:` & `mainfont:` below), this template requires the
#   "Open Sans" (true type or open type) font to be installed. This font is available under
#   the Apache License v.2.0 at <https://fonts.google.com/specimen/Open+Sans>.
#   
# - By default, this template generates a "suggested citation" from the document's
#   metadata according to Chicago Manual of Style (author-date). When not commented
#   out (see `citation-style:` below), this template requires any CSL style from
#   <https://github.com/citation-style-language/styles> to generate the formatted
#   citation.
# 
# For further info, see the [opus4-pdf package documentation](https://github.com/OPUS4/opus4-pdf/).
# 
# 
# Pandoc options
# --------------
# 
documentclass: scrartcl # KOMA-Script class for articles
papersize: a4
pagestyle: empty # don't print page numbers in the footer
sansfont: "Open Sans"
mainfont: "Open Sans"
# fontsize: 10pt # KOMA-Script default: 11pt
number-sections: false # specifies whether numbers should be printed in front of headings
# citation-style: apa.csl # specifies the citation style; defaults to Chicago Manual of Style author-date if commented out
# bibliography: metadata.yaml # specifies the external bibliography (not used since OPUS provides it dynamically); supported formats: BibLaTeX (.bib), BibTeX (.bibtex), CSL JSON (.json), CSL YAML (.yaml)
nocite: |
  @* # specifies that all entries from the bibliography shall be listed
colorlinks: true # specifies whether links should be colored
urlcolor: "blue" # specifies the link color
graphics: true # specifies whether images should be supported (if true, this will insert `\usepackage{graphicx}` in the header includes)
# 
# 
# Custom variables for static content used below
# ----------------------------------------------
# 
images-dir-name: "images"
main-logo-name: "logo.png"
header-left: "Demo Repository"
bibliography-intro: "Suggested citation:"
abstract-heading: "Abstract"
licence-heading: "Terms of use"
licence-intro: "This document is made available under these conditions:"
licence-outro: "For more information see:"
# 
# 
# LaTeX header includes
# ---------------------
# 
header-includes: |
  \special{dvipdfmx:config V 3}
  \special{dvipdfmx:config z 0}
  \usepackage{scrlayer-scrpage}
  \lohead{
    \subsection{$header-left$}
  }
  \cohead{}
  \rohead{
    \subsection{\hfill $config-name$}
    \begin{minipage}[b]{0.30\textwidth}
    \rightline{\small \url{$config-url$}}
    \end{minipage}
  }
  \lofoot{
    $if(licence-text)$\subsubsection{$licence-heading$}$endif$
    \begin{minipage}[t][27mm][t]{0.80\textwidth}
    $if(licence-text)$\tiny $licence-intro$ \newline$endif$
    $if(licence-text)$\tiny \textbf{$licence-text$} \newline$endif$
    $if(licence-url)$$licence-outro$ \newline$endif$
    $if(licence-url)$\url{$licence-url$}$endif$
    \end{minipage}
  }
  \cofoot{
    $if(licence-title)$\subsubsection{\hfill \footnotesize $licence-title$ ~}$endif$
    \begin{minipage}[t][25.4mm][c]{0.01\textwidth}
    \end{minipage}
  }
  \rofoot{
    \begin{minipage}[t][27mm][c]{0.20\textwidth}
    $if(licence-logo-basepath)$$if(licence-logo-name)$\rightline{\includegraphics[width=27mm]{$licence-logo-basepath$$licence-logo-name$}}$endif$$endif$
    \end{minipage}
  }
---


$-- Markdown template with embedded LaTeX \commands & Pandoc template syntax
$-- ------------------------------------------------------------------------

$--SUBHEADER

\begin{figure}
\begin{minipage}[c]{0.37\linewidth}
{\centering 
~
}
\end{minipage}%
%
\begin{minipage}[c]{0.35\linewidth}
{\centering 
~
}
\end{minipage}%
%
\begin{minipage}[c]{0.28\linewidth}
{\centering 
%\includegraphics[width=50mm]{$images-basepath$$images-dir-name$/$main-logo-name$}
}
\end{minipage}%
\end{figure}


$--DOCUMENT METADATA

$if(title)$
# $title$
$endif$


$if(author-meta)$
### $author-meta$
$endif$


$if(bibliography)$
\small $bibliography-intro$ \newline \tiny

::: {#refs}
\small 
:::
$endif$


$if(abstract)$
### $abstract-heading$

\small $abstract$
$endif$
