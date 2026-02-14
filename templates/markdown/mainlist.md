{# mainlist.md is extended by booklist.md and navlist.md #}
{% extends 'base.md' %}

{% block main %}
## Main
{% block hierarchy %}
    {% if it.hierarchy %}
### Hierarchy
        {% for entry in it.hierarchy.parents %}
- <a href="{{entry.navlink}}" title="up">**{{entry.title}}**</a>
        {% endfor %}
        {% if it.hierarchy.current %}
            {% if it.hierarchy.children|length > 0 %}
                {% if it.hierarchy.hastree %}
- <a href="{{it.hierarchy.current.navlink}}" title="collapse">{{it.hierarchy.current.title}}</a>
                {% else %}
- <a href="{{it.hierarchy.current.navlink}}" title="expand">{{it.hierarchy.current.title}}</a>
                {% endif %}
            {% else %}
- {{it.hierarchy.current.title}}
            {% endif %}
        {% endif %}
        {% if it.page != "folder" %}
        {% for entry in it.hierarchy.children %}
            {% if entry.number %}
- <a href="{{entry.navlink}}">{{entry.title}} ({{entry.number}})</a>
            {% else %}
- <a href="{{entry.navlink}}">{{entry.title}}</a>
            {% endif %}
        {% endfor %}
        {% endif %}
    {% endif %}
    {% if it.filters %}
### Filters
- {{it.c.i18n.filtersTitle}}:
        {% for filter in it.filters %}
- <a href="{{filter.navlink}}">{{filter.class}} = {{filter.title}}</a>
        {% endfor %}
    {% endif %}
    {% if it.page == "folder" and it.hierarchy and it.hierarchy.children %}
### {{it.c.i18n.foldersTitle}}
        {% for folder in it.hierarchy.children %}
- <a href="{{folder.navlink}}">{{folder.title|escape}}{% if folder.number %} ({{folder.number}}){% endif %}</a>
        {% endfor %}
    {% endif %}
{% endblock hierarchy %}

{% block content %}
### Content
This is the content block
{% endblock content %}

{% block pager %}
{% if it.isPaginated == 1 %}
### Pager
    {% if it.maxPage > 3 and it.firstLink != "" %}
- <a id="firstLink" href="{{it.firstLink}}">{{it.c.i18n.firstAlt}}</a>
    {% endif %}
    {% if it.prevLink != "" %}
- <a id="prevLink" href="{{it.prevLink}}">{{it.c.i18n.previousAlt}}</a>
    {% endif %}
- {{it.currentPage}} / {{it.maxPage}}
    {% if it.nextLink != "" %}
- <a id="nextLink" href="{{it.nextLink}}">{{it.c.i18n.nextAlt}}</a>
    {% endif %}
    {% if it.maxPage > 3 and it.lastLink != "" %}
- <a id="lastLink" href="{{it.lastLink}}">{{it.c.i18n.lastAlt}}</a>
    {% endif %}
{% endif %}
{% endblock pager %}
{% endblock main %}