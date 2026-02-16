{# bookdetail.md is used for it.page == "book" #}
{% extends 'base.md' %}

{% block main %}
## Main
- Title: {{it.title|escape}}
    {% if it.book.hasCover == 1 %}
- Cover: {{it.book.coverurl}}
- Thumb: {{it.book.thumbnailurl}}
    {% endif %}
- Download:
    {% for data in it.book.datas %}
    * <a title="Download // {{data.format}} {{data.size}}" href="{{data.url}}">Download {{data.format}}</a>
    {% if data.readerUrl != "" %}
    * <a title="Reader // {{data.format}}" href="{{data.readerUrl}}" target="blank">Read {{data.format}}</a>
    {% endif %}
    {% endfor %}
- Authors:
    {% for author in it.book.authors %}{% if not loop.first %}, {% endif %}<a href="{{author.url}}">{{author.name|escape}}</a>{% endfor %}
    {% if it.book.seriesName != "" %}
- Series: <a href="{{it.book.seriesurl}}">{{it.book.seriesName|escape}}</a> ({{it.book.seriesIndex}})
    {% endif %}
    {% if it.book.languagesName != "" %}
- {{it.c.i18n.languagesTitle}}: {{it.book.languagesName}}</span>
    {% endif %}
    {% if it.book.identifiers != "" %}
- {{it.c.i18n.linksTitle}}:
        {% for id in it.book.identifiers %}<a href="{{id.url}}">{{id.name|escape}}</a> {% endfor %}
    {% endif %}
    {% if it.book.tagsName != "" %}
- {{it.c.i18n.tagsTitle}}:
        {% for tag in it.book.tags %}<a href="{{tag.url}}">{{tag.name|escape}}</a> {% endfor %}
    {% endif %}
    {% if it.book.rating != "" %}
- {{it.c.i18n.ratingTitle}}: {{it.book.rating}}
    {% endif %}
    {% if it.book.pages > 0 %}
- {{it.c.i18n.pagesTitle}}: {{it.book.pages}}
    {% endif %}
    {% if it.book.publisherName != "" %}
- {{it.c.i18n.publisherName}}: <a href="{{it.book.publisherurl}}">{{it.book.publisherName|escape}}</a>
    {% endif %}
    {% if it.book.pubDate != "" %}
- {{it.c.i18n.pubdateTitle}}: {{it.book.pubDate}}
    {% endif %}
    {% for column in it.book.customcolumns_preview  %}
- {{column.customColumnType.columnTitle}}: 
            {% if column.htmlvalue != "" and column.url %}
                {# @todo handle series, csv text etc. links #}
                {{column.htmlvalue}}
            {% else %}
                {{column.htmlvalue}}
            {% endif %}
    {% endfor %}
    {% if it.book.folderUrl != "" %}
- {{it.c.i18n.folderTitle}}: <a href="{{it.book.folderUrl}}">{{it.book.folderId|escape}}</a>
    {% endif %}
    {% if it.book.extraFiles != "" %}
- {{it.c.i18n.filesTitle}}: {% for extraFile in it.book.extraFiles %}<a href="{{extraFile.url}}">{{extraFile.name|escape}}</a> {% endfor %}
    {% endif %}

    {% if it.book.content != "" %}
### {{it.c.i18n.contentTitle}}
{{it.book.content|raw}}
    {% endif %}

{% endblock main %}