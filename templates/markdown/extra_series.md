{# extra_series.md is included in booklist.md if it.extra.series is not empty #}

### {{it.c.i18n.seriesTitle}}
{% for series in it.extra.series %}
- <a href="{{series.navlink}}">{{series.title}}</a> ({{series.number}})
{% endfor %}
