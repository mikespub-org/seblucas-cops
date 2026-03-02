{# extra_info.md is included in booklist.md if it.extra.title is not empty #}

### {{it.extra.title}}
{% if it.extra.link %}
  {{it.c.i18n.linkTitle}}: <a rel="external" target="_blank" href="{{it.extra.link}}">{{it.extra.link}}</a>
{% endif %}
{% if it.extra.content %}
  {{it.extra.content|raw}}
{% endif %}
