# {{it.title}}

---
title: {{it.title}}
---

{# only this 'it' variable will be accessible inside page.html #}
{% include 'page.md' with {'it': it.page_it} only %}
