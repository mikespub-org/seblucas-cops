{# navlist.md is used for it.containsBook == 0 #}
{% extends 'mainlist.md' %}

{% block sortlinks %}
{#
{% if it.sortoptions ?? false %}
### Sort Options
{% if it.sortoptions.name is defined %}
- <a href="{{str_format(it.sorturl, "name")}}" title="{{it.sortoptions.name}}">{{it.sortoptions.name}}</a>
{% endif %}
{% if it.sortoptions.count is defined %}
- <a href="{{str_format(it.sorturl, "count")}}" title="{{it.sortoptions.count}}">{{it.sortoptions.count}}</a>
{% endif %}
{% endif %}
#}
{% endblock sortlinks %}

{% block content %}
### Entries
{% for entry in it.entries %}
{% block entry %}
- <a href="{{entry.navlink}}">{{entry.title|escape}} ({{entry.number}})</a>
{% endblock entry %}
{% endfor %}
{% endblock content %}
