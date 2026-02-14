{# booklist.md is used for it.containsBook != 0 #}
{% extends 'mainlist.md' %}

{% block download %}
  {% if it.download %}
    {% for link in it.download %}
- <a href="{{link.url}}" title="{{link.format}}">Download {{link.format}}</a>
    {% endfor %}
  {% endif %}
{% endblock download %}

{% block sortlinks %}
{#
### Sort Options
  {% if it.sortoptions.title is defined %}
- <a href="{{str_format(it.sorturl, "title")}}" title="{{it.sortoptions.title}}">{{it.sortoptions.title}}</a>
  {% endif %}
  {% if it.sortoptions.author is defined %}
- <a href="{{str_format(it.sorturl, "author")}}" title="{{it.sortoptions.author}}">{{it.sortoptions.author}}</a>
  {% endif %}
  {% if it.sortoptions.pubdate is defined %}
- <a href="{{str_format(it.sorturl, "pubdate")}}" title="{{it.sortoptions.pubdate}}">{{it.sortoptions.pubdate}}</a>
  {% endif %}
  {% if it.sortoptions.rating is defined %}
- <a href="{{str_format(it.sorturl, "rating")}}" title="{{it.sortoptions.rating}}">{{it.sortoptions.rating}}</a>
  {% endif %}
  {% if it.sortoptions.timestamp is defined %}
- <a href="{{str_format(it.sorturl, "timestamp")}}" title="{{it.sortoptions.timestamp}}">{{it.sortoptions.timestamp}}</a>
  {% endif %}
#}
{% endblock sortlinks %}

{% block content %}
### {{it.c.i18n.bookwordTitle}}
  {% for entry in it.entries %}
- Title: {{entry.title|escape}}
- Link: {{entry.book.detailurl}}
    {% if entry.thumbnailurl  %}
- Cover: {{entry.thumbnailurl}}
    {% endif %}
- Authors: {{entry.book.authorsName|escape}}
    {% if entry.book.seriesName != "" %}
- Series: <a href="{{entry.book.seriesurl}}">{{entry.book.seriesName|escape}}</a> ({{entry.book.seriesIndex}})
    {% endif %}
    {% for column in entry.book.customcolumns_list  %}
- {{column.customColumnType.columnTitle}} : {{column.htmlvalue}}
    {% endfor %}
- Download:
    {% for data in entry.book.preferedData %}
  * <a href="{{data.url}}">{{data.name}}</a>
    {% endfor %}
  {% endfor %}
{% endblock content %}

{% block extra %}
  {% if it.extra %}
    {% if it.extra.series %}
      {% include 'extra_series.md' %}
    {% endif %}
    {% if it.extra.title %}
      {% include 'extra_info.md' %}
    {% endif %}
  {% endif %}
{% endblock extra %}
