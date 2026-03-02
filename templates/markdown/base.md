{# base.md is extended by all other templates #}
{% block header %}
## Header
  {% if it.parenturl != "" %}
- <a href="{{it.parenturl}}">{{it.fullTitle}}</a>
  {% else %}
- <a href="{{it.homeurl}}">{{it.fullTitle}}</a>
  {% endif %}
  {% block download %}{% endblock download %}
  {% block filterlinks %}
  {% if it.filters  %}
    {% for filter in it.filters %}
- <a href="{{filter.navlink}}">{{filter.class}} {{filter.title}}</a>
    {% endfor %}
  {% else %}
    {% if it.filterurl  %}
      {% if it.containsBook == 0  %}
- <a href="{{it.filterurl}}">{{it.c.i18n.bookwordTitle}}</a>
      {% else %}
- <a href="{{it.filterurl}}">{{it.c.i18n.linksTitle}}</a>
      {% endif %}
    {% endif %}
  {% endif %}
  {% endblock filterlinks %}
- Search form: {{it.baseurl}}?page=query{% if it.databaseId != "" %}&db={{it.databaseId}}{% endif %}{% if it.libraryId != "" %}&vl={{it.libraryId}}{% endif %}&query=TERM
  {% block sortlinks %}{% endblock sortlinks %}
- <a href="{{it.customizeurl}}">{{it.c.i18n.customizeTitle}}</a>
- <a href="{{it.abouturl}}">{{it.c.i18n.aboutTitle}}</a>
{% endblock header %}

{% block main %}
## Main
This is the main block
{% endblock main %}

{% block extra %}{% endblock extra %}

{% block footer %}{% endblock footer %}