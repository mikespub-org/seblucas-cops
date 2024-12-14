# Moving templates from doT to Twig

## Introduction

This is an experiment to convert existing COPS templates from the doT.js syntax to the Twig syntax.
It is based on a one-to-one conversion of the 'bootstrap2' template to the 'twigged' template.

The main use case is for server-side rendering with a well-known and supported template engine.
Client-side rendering is possible with Twig.js as well, but this hasn't been fully tested yet.

References:
- doT:
  * Client-side: See http://olado.github.io/doT/index.html for details of doT.js
  * Server-side: See https://github.com/seblucas/doT-php for doT-php restrictions
- Twig:
  * Server-side: see https://twig.symfony.com/doc/3.x/ for Twig documentation
  * Client-side: see https://github.com/twigjs/twig.js/wiki/Implementation-Notes for Twig features supported by twig.js

## Basic Cheatsheet

| Feature | doT syntax | Twig syntax | Remark |
|---------|------------|-------------|--------|
| Dot Notation | it.data.entry | same | |
| Interpolate | {{= it.title }} | {{ it.title }} | |
| Include/use | {{#def:header}} | {% include 'header.html' %} | use include statement |
| Conditional | {{? it.containsBook == 0}}<br>...<br>{{??}}<br>...<br>{{?}} | {% if it.containsBook == 0 %}<br>...<br>{% else %}<br>...<br>{% endif %} | |
| AND clause | {{? entry.class == "" && entry.number == ""}} | {% if entry.class == "" and entry.number == "" %} | |
| OR clause | {{? it.page == "book" \|\| it.page == "about"}} | {% if it.page == "book" or it.page == "about" %} | |
| Iterate | {{\~entry.book.preferedData:data:i}}<br>...<br>{{\~}} | {% for data in entry.book.preferedData %}<br>...<br>{% endfor %} | |
| first iteration | {{? i == 0}} | {% if loop.first %} | |
| last iteration | {{? i + 1 == entry.book.preferedCount}} | {% if loop.last %} | |
| Functions | str_format(it.sorturl, "title") | same | for defined Twig functions |
|  | htmlspecialchars(entry.title) | entry.title\|escape | for defined Twig filters |
|  | it.book.content | it.book.content\|raw | for pre-formatted HTML |
|  | entry.book.preferedData.length | entry.book.preferedCount | not supported in doT-php |
|  | {{=it.assets}}/whatever.js?v={{=it.version}} | {{asset('whatever.js')}} | quote issues in doT-php |
| Evaluate | {{ ... }} | N/A | not supported in doT-php |
| Encode | {{! it.title }} | N/A | not supported in doT-php |
| Define | {{##def:snippet: ... #}} | N/A | not supported in doT-php |

## Templates and Inheritance

1. [index.html](index.html) is rendered server-side to generate the initial HTML page
2. [page.html](page.html) is rendered client-side or server-side for each request, and it decides which template to include next
3. [base.html](base.html) is extended by all other templates:
  - [about.html](about.html) (page=about)
  - [bookdetail.html](bookdetail.html) (page=book)
  - [mainlist.html](mainlist.html)
    - [booklist.html](booklist.html)
      - [recent.html](recent.html) (page=recent)
      - [authordetail.html](authordetail.html) (page=author)
      - [customdetail.html](customdetail.html) (page=custom)
      - [identifierdetail.html](identifierdetail.html) (page=identifier)
      - [languagedetail.html](languagedetail.html) (page=language)
      - [publisherdetail.html](publisherdetail.html) (page=publisher)
      - [ratingdetail.html](ratingdetail.html) (page=rating)
      - [seriedetail.html](seriedetail.html) (page=serie)
      - [tagdetail.html](tagdetail.html) (page=tag)
      - include [extra_series.html](extra_series.html) and/or [extra_info.html](extra_info.html) if it.extra is available
    - [navlist.html](navlist.html)
      - [customize.html](customize.html) (page=customize)
    - [filters.html](filters.html) (filter=1)
