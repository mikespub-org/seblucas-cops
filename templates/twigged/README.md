# Moving templates from doT to Twig

## Introduction

This is an experiment to convert existing COPS templates from the doT.js syntax to the Twig syntax.
It is based on a one-to-one conversion of the 'bootstrap2' template to the 'twigged' template.

The main use case is for server-side rendering with a well-known and supported template engine.
Client-side rendering might be possible with Twig.js as well, but this hasn't been tested yet.

References:
- See http://olado.github.io/doT/index.html for details of doT.js
- See https://github.com/seblucas/doT-php for doT-php restrictions
- See https://twig.symfony.com/doc/3.x/ for Twig documentation

## Basic Cheatsheet

| Feature | doT syntax | Twig syntax | Remark |
|---------|------------|-------------|--------|
| Dot Notation | it.data.entry | same | |
| Interpolate | {{= it.title }} | {{ it.title }} | |
| Include/use | {{#def:header}} | {{ include('header.html') }} | server-side rendering |
| Conditional | {{? it.containsBook == 0}}<br>...<br>{{??}}<br>...<br>{{?}} | {% if it.containsBook == 0 %}<br>...<br>{% else %}<br>...<br>{% endif %} | |
| AND clause | {{? entry.navlink == "#" && entry.number == ""}} | {% if entry.navlink == "#" and entry.number == "" %} | |
| OR clause | {{? it.page == 13 \|\| it.page == 16}} | {% if it.page == 13 or it.page == 16 %} | |
| Iterate | {{~entry.book.preferedData:data:i}}<br>...<br>{{~}} | {% for data in entry.book.preferedData %}<br>...<br>{% endfor %} | |
| first iteration | {{? i == 0}} | {% if loop.first %} | |
| last iteration | {{? i + 1 == entry.book.preferedCount}} | {% if loop.last %} | |
| Functions | str_format(it.sorturl, "title") | same | for defined Twig functions |
|  | htmlspecialchars(entry.title) | entry.title\|escape | for defined Twig filters |
|  | it.book.content | it.book.content\|raw | for pre-formatted HTML |
|  | entry.book.preferedData.length | entry.book.preferedCount | not supported in doT-php |
| Evaluate | {{ ... }} | N/A | not supported in doT-php |
| Encode | {{! it.title }} | N/A | not supported in doT-php |
| Define | {{##def:snippet: ... #}} | N/A | not supported in doT-php |

