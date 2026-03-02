{# page.md is rendered by Twig and it decides which template to include #}
{% if it.page == "book" %}
    {% include 'bookdetail.md' %}
{% elseif it.page == "about" %}
    {% include 'about.md' %}
{% elseif it.page == "customize" %}
    {% include 'customize.md' %}
{% elseif it.isFilterPage %}
    {% include 'filters.md' %}
{% elseif it.containsBook == 0 %}
    {% include 'navlist.md' %}
{% elseif it.page == "recent" %}
    {% include 'recent.md' %}
{% else %}
    {% include 'booklist.md' %}
{% endif %}