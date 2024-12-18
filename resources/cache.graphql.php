<?php
return array (
  'loc' => 
  array (
    'start' => 0,
    'end' => 6644,
  ),
  'kind' => 'Document',
  'definitions' => 
  array (
    0 => 
    array (
      'loc' => 
      array (
        'start' => 0,
        'end' => 5129,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 131,
          'end' => 136,
        ),
        'kind' => 'Name',
        'value' => 'Query',
      ),
      'interfaces' => 
      array (
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 141,
            'end' => 327,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 141,
              'end' => 148,
            ),
            'kind' => 'Name',
            'value' => 'authors',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 154,
                'end' => 164,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 154,
                  'end' => 159,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 161,
                  'end' => 164,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 161,
                    'end' => 164,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 169,
                'end' => 182,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 169,
                  'end' => 174,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 176,
                  'end' => 182,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 176,
                    'end' => 182,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 188,
                'end' => 298,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 278,
                  'end' => 283,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 285,
                  'end' => 298,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 285,
                    'end' => 298,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 188,
                  'end' => 273,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 303,
                'end' => 314,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 303,
                  'end' => 309,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 311,
                  'end' => 314,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 311,
                    'end' => 314,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 320,
              'end' => 327,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 321,
                'end' => 326,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 321,
                  'end' => 326,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 330,
            'end' => 475,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 330,
              'end' => 336,
            ),
            'kind' => 'Name',
            'value' => 'author',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 342,
                'end' => 348,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 342,
                  'end' => 344,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 346,
                  'end' => 348,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 346,
                    'end' => 348,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 354,
                'end' => 464,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 444,
                  'end' => 449,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 451,
                  'end' => 464,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 451,
                    'end' => 464,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 354,
                  'end' => 439,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 470,
              'end' => 475,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 470,
                'end' => 475,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 478,
            'end' => 666,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 478,
              'end' => 483,
            ),
            'kind' => 'Name',
            'value' => 'books',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 489,
                'end' => 499,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 489,
                  'end' => 494,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 496,
                  'end' => 499,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 496,
                    'end' => 499,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 504,
                'end' => 517,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 504,
                  'end' => 509,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 511,
                  'end' => 517,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 511,
                    'end' => 517,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 523,
                'end' => 633,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 613,
                  'end' => 618,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 620,
                  'end' => 633,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 620,
                    'end' => 633,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 523,
                  'end' => 608,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 638,
                'end' => 649,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 638,
                  'end' => 644,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 646,
                  'end' => 649,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 646,
                    'end' => 649,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 655,
              'end' => 666,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 656,
                'end' => 665,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 656,
                  'end' => 665,
                ),
                'kind' => 'Name',
                'value' => 'EntryBook',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 669,
            'end' => 816,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 669,
              'end' => 673,
            ),
            'kind' => 'Name',
            'value' => 'book',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 679,
                'end' => 685,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 679,
                  'end' => 681,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 683,
                  'end' => 685,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 683,
                    'end' => 685,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 691,
                'end' => 801,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 781,
                  'end' => 786,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 788,
                  'end' => 801,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 788,
                    'end' => 801,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 691,
                  'end' => 776,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 807,
              'end' => 816,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 807,
                'end' => 816,
              ),
              'kind' => 'Name',
              'value' => 'EntryBook',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 819,
            'end' => 1011,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 819,
              'end' => 832,
            ),
            'kind' => 'Name',
            'value' => 'customColumns',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 838,
                'end' => 848,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 838,
                  'end' => 843,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 845,
                  'end' => 848,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 845,
                    'end' => 848,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 853,
                'end' => 866,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 853,
                  'end' => 858,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 860,
                  'end' => 866,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 860,
                    'end' => 866,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 872,
                'end' => 982,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 962,
                  'end' => 967,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 969,
                  'end' => 982,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 969,
                    'end' => 982,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 872,
                  'end' => 957,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 987,
                'end' => 998,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 987,
                  'end' => 993,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 995,
                  'end' => 998,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 995,
                    'end' => 998,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1004,
              'end' => 1011,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1005,
                'end' => 1010,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1005,
                  'end' => 1010,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 1014,
            'end' => 1165,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1014,
              'end' => 1026,
            ),
            'kind' => 'Name',
            'value' => 'customColumn',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1032,
                'end' => 1038,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1032,
                  'end' => 1034,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1036,
                  'end' => 1038,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1036,
                    'end' => 1038,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1044,
                'end' => 1154,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1134,
                  'end' => 1139,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1141,
                  'end' => 1154,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1141,
                    'end' => 1154,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1044,
                  'end' => 1129,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1160,
              'end' => 1165,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1160,
                'end' => 1165,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 1168,
            'end' => 1317,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1168,
              'end' => 1173,
            ),
            'kind' => 'Name',
            'value' => 'datas',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1179,
                'end' => 1189,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1179,
                  'end' => 1185,
                ),
                'kind' => 'Name',
                'value' => 'bookId',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1187,
                  'end' => 1189,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1187,
                    'end' => 1189,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1195,
                'end' => 1305,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1285,
                  'end' => 1290,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1292,
                  'end' => 1305,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1292,
                    'end' => 1305,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1195,
                  'end' => 1280,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1311,
              'end' => 1317,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1312,
                'end' => 1316,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1312,
                  'end' => 1316,
                ),
                'kind' => 'Name',
                'value' => 'Data',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        7 => 
        array (
          'loc' => 
          array (
            'start' => 1320,
            'end' => 1462,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1320,
              'end' => 1324,
            ),
            'kind' => 'Name',
            'value' => 'data',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1330,
                'end' => 1336,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1330,
                  'end' => 1332,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1334,
                  'end' => 1336,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1334,
                    'end' => 1336,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1342,
                'end' => 1452,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1432,
                  'end' => 1437,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1439,
                  'end' => 1452,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1439,
                    'end' => 1452,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1342,
                  'end' => 1427,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1458,
              'end' => 1462,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1458,
                'end' => 1462,
              ),
              'kind' => 'Name',
              'value' => 'Data',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        8 => 
        array (
          'loc' => 
          array (
            'start' => 1465,
            'end' => 1649,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1465,
              'end' => 1470,
            ),
            'kind' => 'Name',
            'value' => 'feeds',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1476,
                'end' => 1486,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1476,
                  'end' => 1481,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1483,
                  'end' => 1486,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1483,
                    'end' => 1486,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1491,
                'end' => 1504,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1491,
                  'end' => 1496,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1498,
                  'end' => 1504,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1498,
                    'end' => 1504,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 1510,
                'end' => 1620,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1600,
                  'end' => 1605,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1607,
                  'end' => 1620,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1607,
                    'end' => 1620,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1510,
                  'end' => 1595,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 1625,
                'end' => 1636,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1625,
                  'end' => 1631,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1633,
                  'end' => 1636,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1633,
                    'end' => 1636,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1642,
              'end' => 1649,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1643,
                'end' => 1648,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1643,
                  'end' => 1648,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        9 => 
        array (
          'loc' => 
          array (
            'start' => 1652,
            'end' => 1795,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1652,
              'end' => 1656,
            ),
            'kind' => 'Name',
            'value' => 'feed',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1662,
                'end' => 1668,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1662,
                  'end' => 1664,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1666,
                  'end' => 1668,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1666,
                    'end' => 1668,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1674,
                'end' => 1784,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1764,
                  'end' => 1769,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1771,
                  'end' => 1784,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1771,
                    'end' => 1784,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1674,
                  'end' => 1759,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1790,
              'end' => 1795,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1790,
                'end' => 1795,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        10 => 
        array (
          'loc' => 
          array (
            'start' => 1798,
            'end' => 1984,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1798,
              'end' => 1805,
            ),
            'kind' => 'Name',
            'value' => 'formats',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1811,
                'end' => 1821,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1811,
                  'end' => 1816,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1818,
                  'end' => 1821,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1818,
                    'end' => 1821,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1826,
                'end' => 1839,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1826,
                  'end' => 1831,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1833,
                  'end' => 1839,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1833,
                    'end' => 1839,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 1845,
                'end' => 1955,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1935,
                  'end' => 1940,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1942,
                  'end' => 1955,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1942,
                    'end' => 1955,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1845,
                  'end' => 1930,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 1960,
                'end' => 1971,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1960,
                  'end' => 1966,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1968,
                  'end' => 1971,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1968,
                    'end' => 1971,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1977,
              'end' => 1984,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1978,
                'end' => 1983,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1978,
                  'end' => 1983,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        11 => 
        array (
          'loc' => 
          array (
            'start' => 1987,
            'end' => 2132,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1987,
              'end' => 1993,
            ),
            'kind' => 'Name',
            'value' => 'format',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1999,
                'end' => 2005,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1999,
                  'end' => 2001,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2003,
                  'end' => 2005,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2003,
                    'end' => 2005,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2011,
                'end' => 2121,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2101,
                  'end' => 2106,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2108,
                  'end' => 2121,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2108,
                    'end' => 2121,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2011,
                  'end' => 2096,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2127,
              'end' => 2132,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2127,
                'end' => 2132,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        12 => 
        array (
          'loc' => 
          array (
            'start' => 2135,
            'end' => 2325,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2135,
              'end' => 2146,
            ),
            'kind' => 'Name',
            'value' => 'identifiers',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2152,
                'end' => 2162,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2152,
                  'end' => 2157,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2159,
                  'end' => 2162,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2159,
                    'end' => 2162,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2167,
                'end' => 2180,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2167,
                  'end' => 2172,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2174,
                  'end' => 2180,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2174,
                    'end' => 2180,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 2186,
                'end' => 2296,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2276,
                  'end' => 2281,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2283,
                  'end' => 2296,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2283,
                    'end' => 2296,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2186,
                  'end' => 2271,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2301,
                'end' => 2312,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2301,
                  'end' => 2307,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2309,
                  'end' => 2312,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2309,
                    'end' => 2312,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2318,
              'end' => 2325,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 2319,
                'end' => 2324,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2319,
                  'end' => 2324,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        13 => 
        array (
          'loc' => 
          array (
            'start' => 2328,
            'end' => 2477,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2328,
              'end' => 2338,
            ),
            'kind' => 'Name',
            'value' => 'identifier',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2344,
                'end' => 2350,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2344,
                  'end' => 2346,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2348,
                  'end' => 2350,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2348,
                    'end' => 2350,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2356,
                'end' => 2466,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2446,
                  'end' => 2451,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2453,
                  'end' => 2466,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2453,
                    'end' => 2466,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2356,
                  'end' => 2441,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2472,
              'end' => 2477,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2472,
                'end' => 2477,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        14 => 
        array (
          'loc' => 
          array (
            'start' => 2480,
            'end' => 2668,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2480,
              'end' => 2489,
            ),
            'kind' => 'Name',
            'value' => 'languages',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2495,
                'end' => 2505,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2495,
                  'end' => 2500,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2502,
                  'end' => 2505,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2502,
                    'end' => 2505,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2510,
                'end' => 2523,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2510,
                  'end' => 2515,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2517,
                  'end' => 2523,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2517,
                    'end' => 2523,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 2529,
                'end' => 2639,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2619,
                  'end' => 2624,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2626,
                  'end' => 2639,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2626,
                    'end' => 2639,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2529,
                  'end' => 2614,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2644,
                'end' => 2655,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2644,
                  'end' => 2650,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2652,
                  'end' => 2655,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2652,
                    'end' => 2655,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2661,
              'end' => 2668,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 2662,
                'end' => 2667,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2662,
                  'end' => 2667,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        15 => 
        array (
          'loc' => 
          array (
            'start' => 2671,
            'end' => 2818,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2671,
              'end' => 2679,
            ),
            'kind' => 'Name',
            'value' => 'language',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2685,
                'end' => 2691,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2685,
                  'end' => 2687,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2689,
                  'end' => 2691,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2689,
                    'end' => 2691,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2697,
                'end' => 2807,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2787,
                  'end' => 2792,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2794,
                  'end' => 2807,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2794,
                    'end' => 2807,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2697,
                  'end' => 2782,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2813,
              'end' => 2818,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2813,
                'end' => 2818,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        16 => 
        array (
          'loc' => 
          array (
            'start' => 2821,
            'end' => 3011,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2821,
              'end' => 2832,
            ),
            'kind' => 'Name',
            'value' => 'preferences',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2838,
                'end' => 2848,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2838,
                  'end' => 2843,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2845,
                  'end' => 2848,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2845,
                    'end' => 2848,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2853,
                'end' => 2866,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2853,
                  'end' => 2858,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2860,
                  'end' => 2866,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2860,
                    'end' => 2866,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 2872,
                'end' => 2982,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2962,
                  'end' => 2967,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2969,
                  'end' => 2982,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2969,
                    'end' => 2982,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2872,
                  'end' => 2957,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2987,
                'end' => 2998,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2987,
                  'end' => 2993,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2995,
                  'end' => 2998,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2995,
                    'end' => 2998,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3004,
              'end' => 3011,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3005,
                'end' => 3010,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3005,
                  'end' => 3010,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        17 => 
        array (
          'loc' => 
          array (
            'start' => 3014,
            'end' => 3163,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3014,
              'end' => 3024,
            ),
            'kind' => 'Name',
            'value' => 'preference',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3030,
                'end' => 3036,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3030,
                  'end' => 3032,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3034,
                  'end' => 3036,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3034,
                    'end' => 3036,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3042,
                'end' => 3152,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3132,
                  'end' => 3137,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3139,
                  'end' => 3152,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3139,
                    'end' => 3152,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3042,
                  'end' => 3127,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3158,
              'end' => 3163,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3158,
                'end' => 3163,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        18 => 
        array (
          'loc' => 
          array (
            'start' => 3166,
            'end' => 3355,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3166,
              'end' => 3176,
            ),
            'kind' => 'Name',
            'value' => 'publishers',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3182,
                'end' => 3192,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3182,
                  'end' => 3187,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3189,
                  'end' => 3192,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3189,
                    'end' => 3192,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3197,
                'end' => 3210,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3197,
                  'end' => 3202,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3204,
                  'end' => 3210,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3204,
                    'end' => 3210,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 3216,
                'end' => 3326,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3306,
                  'end' => 3311,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3313,
                  'end' => 3326,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3313,
                    'end' => 3326,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3216,
                  'end' => 3301,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 3331,
                'end' => 3342,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3331,
                  'end' => 3337,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3339,
                  'end' => 3342,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3339,
                    'end' => 3342,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3348,
              'end' => 3355,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3349,
                'end' => 3354,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3349,
                  'end' => 3354,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        19 => 
        array (
          'loc' => 
          array (
            'start' => 3358,
            'end' => 3506,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3358,
              'end' => 3367,
            ),
            'kind' => 'Name',
            'value' => 'publisher',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3373,
                'end' => 3379,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3373,
                  'end' => 3375,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3377,
                  'end' => 3379,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3377,
                    'end' => 3379,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3385,
                'end' => 3495,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3475,
                  'end' => 3480,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3482,
                  'end' => 3495,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3482,
                    'end' => 3495,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3385,
                  'end' => 3470,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3501,
              'end' => 3506,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3501,
                'end' => 3506,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        20 => 
        array (
          'loc' => 
          array (
            'start' => 3509,
            'end' => 3695,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3509,
              'end' => 3516,
            ),
            'kind' => 'Name',
            'value' => 'ratings',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3522,
                'end' => 3532,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3522,
                  'end' => 3527,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3529,
                  'end' => 3532,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3529,
                    'end' => 3532,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3537,
                'end' => 3550,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3537,
                  'end' => 3542,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3544,
                  'end' => 3550,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3544,
                    'end' => 3550,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 3556,
                'end' => 3666,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3646,
                  'end' => 3651,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3653,
                  'end' => 3666,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3653,
                    'end' => 3666,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3556,
                  'end' => 3641,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 3671,
                'end' => 3682,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3671,
                  'end' => 3677,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3679,
                  'end' => 3682,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3679,
                    'end' => 3682,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3688,
              'end' => 3695,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3689,
                'end' => 3694,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3689,
                  'end' => 3694,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        21 => 
        array (
          'loc' => 
          array (
            'start' => 3698,
            'end' => 3843,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3698,
              'end' => 3704,
            ),
            'kind' => 'Name',
            'value' => 'rating',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3710,
                'end' => 3716,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3710,
                  'end' => 3712,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3714,
                  'end' => 3716,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3714,
                    'end' => 3716,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3722,
                'end' => 3832,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3812,
                  'end' => 3817,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3819,
                  'end' => 3832,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3819,
                    'end' => 3832,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3722,
                  'end' => 3807,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3838,
              'end' => 3843,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3838,
                'end' => 3843,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        22 => 
        array (
          'loc' => 
          array (
            'start' => 3846,
            'end' => 4031,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3846,
              'end' => 3852,
            ),
            'kind' => 'Name',
            'value' => 'series',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3858,
                'end' => 3868,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3858,
                  'end' => 3863,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3865,
                  'end' => 3868,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3865,
                    'end' => 3868,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3873,
                'end' => 3886,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3873,
                  'end' => 3878,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3880,
                  'end' => 3886,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3880,
                    'end' => 3886,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 3892,
                'end' => 4002,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3982,
                  'end' => 3987,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3989,
                  'end' => 4002,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3989,
                    'end' => 4002,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3892,
                  'end' => 3977,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 4007,
                'end' => 4018,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4007,
                  'end' => 4013,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4015,
                  'end' => 4018,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4015,
                    'end' => 4018,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4024,
              'end' => 4031,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4025,
                'end' => 4030,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4025,
                  'end' => 4030,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        23 => 
        array (
          'loc' => 
          array (
            'start' => 4034,
            'end' => 4178,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4034,
              'end' => 4039,
            ),
            'kind' => 'Name',
            'value' => 'serie',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4045,
                'end' => 4051,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4045,
                  'end' => 4047,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4049,
                  'end' => 4051,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4049,
                    'end' => 4051,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4057,
                'end' => 4167,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4147,
                  'end' => 4152,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4154,
                  'end' => 4167,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4154,
                    'end' => 4167,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4057,
                  'end' => 4142,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4173,
              'end' => 4178,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 4173,
                'end' => 4178,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        24 => 
        array (
          'loc' => 
          array (
            'start' => 4181,
            'end' => 4364,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4181,
              'end' => 4185,
            ),
            'kind' => 'Name',
            'value' => 'tags',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4191,
                'end' => 4201,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4191,
                  'end' => 4196,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4198,
                  'end' => 4201,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4198,
                    'end' => 4201,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4206,
                'end' => 4219,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4206,
                  'end' => 4211,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4213,
                  'end' => 4219,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4213,
                    'end' => 4219,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 4225,
                'end' => 4335,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4315,
                  'end' => 4320,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4322,
                  'end' => 4335,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4322,
                    'end' => 4335,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4225,
                  'end' => 4310,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 4340,
                'end' => 4351,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4340,
                  'end' => 4346,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4348,
                  'end' => 4351,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4348,
                    'end' => 4351,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4357,
              'end' => 4364,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4358,
                'end' => 4363,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4358,
                  'end' => 4363,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        25 => 
        array (
          'loc' => 
          array (
            'start' => 4367,
            'end' => 4509,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4367,
              'end' => 4370,
            ),
            'kind' => 'Name',
            'value' => 'tag',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4376,
                'end' => 4382,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4376,
                  'end' => 4378,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4380,
                  'end' => 4382,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4380,
                    'end' => 4382,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4388,
                'end' => 4498,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4478,
                  'end' => 4483,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4485,
                  'end' => 4498,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4485,
                    'end' => 4498,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4388,
                  'end' => 4473,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4504,
              'end' => 4509,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 4504,
                'end' => 4509,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        26 => 
        array (
          'loc' => 
          array (
            'start' => 4512,
            'end' => 4676,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4649,
              'end' => 4653,
            ),
            'kind' => 'Name',
            'value' => 'node',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4659,
                'end' => 4666,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4659,
                  'end' => 4661,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4663,
                  'end' => 4666,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4663,
                    'end' => 4665,
                  ),
                  'kind' => 'NamedType',
                  'name' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 4663,
                      'end' => 4665,
                    ),
                    'kind' => 'Name',
                    'value' => 'ID',
                  ),
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4672,
              'end' => 4676,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 4672,
                'end' => 4676,
              ),
              'kind' => 'Name',
              'value' => 'Node',
            ),
          ),
          'directives' => 
          array (
          ),
          'description' => 
          array (
            'loc' => 
            array (
              'start' => 4512,
              'end' => 4646,
            ),
            'kind' => 'StringValue',
            'value' => 'Node root field with Global Object Identifier
See https://relay.dev/graphql/objectidentification.htm#sec-Node-root-field',
            'block' => true,
          ),
        ),
        27 => 
        array (
          'loc' => 
          array (
            'start' => 4679,
            'end' => 4855,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4815,
              'end' => 4823,
            ),
            'kind' => 'Name',
            'value' => 'nodelist',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4829,
                'end' => 4843,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4829,
                  'end' => 4835,
                ),
                'kind' => 'Name',
                'value' => 'idlist',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4837,
                  'end' => 4843,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4837,
                    'end' => 4842,
                  ),
                  'kind' => 'ListType',
                  'type' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 4838,
                      'end' => 4841,
                    ),
                    'kind' => 'NonNullType',
                    'type' => 
                    array (
                      'loc' => 
                      array (
                        'start' => 4838,
                        'end' => 4840,
                      ),
                      'kind' => 'NamedType',
                      'name' => 
                      array (
                        'loc' => 
                        array (
                          'start' => 4838,
                          'end' => 4840,
                        ),
                        'kind' => 'Name',
                        'value' => 'ID',
                      ),
                    ),
                  ),
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4849,
              'end' => 4855,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4850,
                'end' => 4854,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4850,
                  'end' => 4854,
                ),
                'kind' => 'Name',
                'value' => 'Node',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
          'description' => 
          array (
            'loc' => 
            array (
              'start' => 4679,
              'end' => 4812,
            ),
            'kind' => 'StringValue',
            'value' => 'Plural identifying root field
See https://relay.dev/graphql/objectidentification.htm#sec-Plural-identifying-root-fields',
            'block' => true,
          ),
        ),
        28 => 
        array (
          'loc' => 
          array (
            'start' => 4858,
            'end' => 5127,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4898,
              'end' => 4904,
            ),
            'kind' => 'Name',
            'value' => 'search',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4910,
                'end' => 4924,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4910,
                  'end' => 4915,
                ),
                'kind' => 'Name',
                'value' => 'query',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4917,
                  'end' => 4924,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4917,
                    'end' => 4923,
                  ),
                  'kind' => 'NamedType',
                  'name' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 4917,
                      'end' => 4923,
                    ),
                    'kind' => 'Name',
                    'value' => 'String',
                  ),
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4929,
                'end' => 4942,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4929,
                  'end' => 4934,
                ),
                'kind' => 'Name',
                'value' => 'scope',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4936,
                  'end' => 4942,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4936,
                    'end' => 4942,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 4947,
                'end' => 4957,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4947,
                  'end' => 4952,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4954,
                  'end' => 4957,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4954,
                    'end' => 4957,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 4962,
                'end' => 4975,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4962,
                  'end' => 4967,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4969,
                  'end' => 4975,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4969,
                    'end' => 4975,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            4 => 
            array (
              'loc' => 
              array (
                'start' => 4981,
                'end' => 5091,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5071,
                  'end' => 5076,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5078,
                  'end' => 5091,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5078,
                    'end' => 5091,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4981,
                  'end' => 5066,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            5 => 
            array (
              'loc' => 
              array (
                'start' => 5096,
                'end' => 5107,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5096,
                  'end' => 5102,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5104,
                  'end' => 5107,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5104,
                    'end' => 5107,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5113,
              'end' => 5127,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5114,
                'end' => 5126,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5114,
                  'end' => 5126,
                ),
                'kind' => 'Name',
                'value' => 'SearchResult',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
          'description' => 
          array (
            'loc' => 
            array (
              'start' => 4858,
              'end' => 4895,
            ),
            'kind' => 'StringValue',
            'value' => 'Search by query and scope',
            'block' => true,
          ),
        ),
      ),
      'description' => 
      array (
        'loc' => 
        array (
          'start' => 0,
          'end' => 125,
        ),
        'kind' => 'StringValue',
        'value' => 'Adapted from https://github.com/mikespub-org/acdibble-tuql
Goal: create GraphQL interface to Calibre database (maybe)',
        'block' => true,
      ),
    ),
    1 => 
    array (
      'loc' => 
      array (
        'start' => 5131,
        'end' => 5211,
      ),
      'kind' => 'ScalarTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5198,
          'end' => 5211,
        ),
        'kind' => 'Name',
        'value' => 'SequelizeJSON',
      ),
      'directives' => 
      array (
      ),
      'description' => 
      array (
        'loc' => 
        array (
          'start' => 5131,
          'end' => 5190,
        ),
        'kind' => 'StringValue',
        'value' => 'The `JSON` scalar type represents raw JSON as values.',
        'block' => true,
      ),
    ),
    2 => 
    array (
      'loc' => 
      array (
        'start' => 5213,
        'end' => 5368,
      ),
      'kind' => 'InterfaceTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5350,
          'end' => 5354,
        ),
        'kind' => 'Name',
        'value' => 'Node',
      ),
      'directives' => 
      array (
      ),
      'interfaces' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 5359,
            'end' => 5366,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5359,
              'end' => 5361,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5363,
              'end' => 5366,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5363,
                'end' => 5365,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5363,
                  'end' => 5365,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
      'description' => 
      array (
        'loc' => 
        array (
          'start' => 5213,
          'end' => 5339,
        ),
        'kind' => 'StringValue',
        'value' => 'Node Interface with Global Object Identifier
See https://relay.dev/graphql/objectidentification.htm#sec-Node-Interface',
        'block' => true,
      ),
    ),
    3 => 
    array (
      'loc' => 
      array (
        'start' => 5370,
        'end' => 5775,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5375,
          'end' => 5380,
        ),
        'kind' => 'Name',
        'value' => 'Entry',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 5392,
            'end' => 5396,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5392,
              'end' => 5396,
            ),
            'kind' => 'Name',
            'value' => 'Node',
          ),
        ),
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 5401,
            'end' => 5408,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5401,
              'end' => 5403,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5405,
              'end' => 5408,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5405,
                'end' => 5407,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5405,
                  'end' => 5407,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 5411,
            'end' => 5425,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5411,
              'end' => 5416,
            ),
            'kind' => 'Name',
            'value' => 'title',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5418,
              'end' => 5425,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5418,
                'end' => 5424,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5418,
                  'end' => 5424,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 5428,
            'end' => 5443,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5428,
              'end' => 5435,
            ),
            'kind' => 'Name',
            'value' => 'content',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5437,
              'end' => 5443,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5437,
                'end' => 5443,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 5446,
            'end' => 5465,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5446,
              'end' => 5457,
            ),
            'kind' => 'Name',
            'value' => 'contentType',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5459,
              'end' => 5465,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5459,
                'end' => 5465,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 5468,
            'end' => 5485,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5468,
              'end' => 5477,
            ),
            'kind' => 'Name',
            'value' => 'linkArray',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5479,
              'end' => 5485,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5480,
                'end' => 5484,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5480,
                  'end' => 5484,
                ),
                'kind' => 'Name',
                'value' => 'Link',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 5488,
            'end' => 5505,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5488,
              'end' => 5497,
            ),
            'kind' => 'Name',
            'value' => 'className',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5499,
              'end' => 5505,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5499,
                'end' => 5505,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 5508,
            'end' => 5531,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5508,
              'end' => 5523,
            ),
            'kind' => 'Name',
            'value' => 'numberOfElement',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5525,
              'end' => 5531,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5525,
                'end' => 5531,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        7 => 
        array (
          'loc' => 
          array (
            'start' => 5534,
            'end' => 5722,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5534,
              'end' => 5539,
            ),
            'kind' => 'Name',
            'value' => 'books',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 5545,
                'end' => 5555,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5545,
                  'end' => 5550,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5552,
                  'end' => 5555,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5552,
                    'end' => 5555,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 5560,
                'end' => 5573,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5560,
                  'end' => 5565,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5567,
                  'end' => 5573,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5567,
                    'end' => 5573,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 5579,
                'end' => 5689,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5669,
                  'end' => 5674,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5676,
                  'end' => 5689,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5676,
                    'end' => 5689,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 5579,
                  'end' => 5664,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 5694,
                'end' => 5705,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5694,
                  'end' => 5700,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5702,
                  'end' => 5705,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5702,
                    'end' => 5705,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5711,
              'end' => 5722,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5712,
                'end' => 5721,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5712,
                  'end' => 5721,
                ),
                'kind' => 'Name',
                'value' => 'EntryBook',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        8 => 
        array (
          'loc' => 
          array (
            'start' => 5725,
            'end' => 5740,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5725,
              'end' => 5732,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5734,
              'end' => 5740,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5734,
                'end' => 5740,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        9 => 
        array (
          'loc' => 
          array (
            'start' => 5743,
            'end' => 5760,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5743,
              'end' => 5752,
            ),
            'kind' => 'Name',
            'value' => 'thumbnail',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5754,
              'end' => 5760,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5754,
                'end' => 5760,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        10 => 
        array (
          'loc' => 
          array (
            'start' => 5763,
            'end' => 5773,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5763,
              'end' => 5767,
            ),
            'kind' => 'Name',
            'value' => 'note',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5769,
              'end' => 5773,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5769,
                'end' => 5773,
              ),
              'kind' => 'Name',
              'value' => 'Note',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    4 => 
    array (
      'loc' => 
      array (
        'start' => 5777,
        'end' => 6187,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5782,
          'end' => 5791,
        ),
        'kind' => 'Name',
        'value' => 'EntryBook',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 5803,
            'end' => 5807,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5803,
              'end' => 5807,
            ),
            'kind' => 'Name',
            'value' => 'Node',
          ),
        ),
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 5812,
            'end' => 5819,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5812,
              'end' => 5814,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5816,
              'end' => 5819,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5816,
                'end' => 5818,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5816,
                  'end' => 5818,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 5822,
            'end' => 5836,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5822,
              'end' => 5827,
            ),
            'kind' => 'Name',
            'value' => 'title',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5829,
              'end' => 5836,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5829,
                'end' => 5835,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5829,
                  'end' => 5835,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 5839,
            'end' => 5854,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5839,
              'end' => 5846,
            ),
            'kind' => 'Name',
            'value' => 'content',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5848,
              'end' => 5854,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5848,
                'end' => 5854,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 5857,
            'end' => 5876,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5857,
              'end' => 5868,
            ),
            'kind' => 'Name',
            'value' => 'contentType',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5870,
              'end' => 5876,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5870,
                'end' => 5876,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 5879,
            'end' => 5896,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5879,
              'end' => 5888,
            ),
            'kind' => 'Name',
            'value' => 'linkArray',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5890,
              'end' => 5896,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5891,
                'end' => 5895,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5891,
                  'end' => 5895,
                ),
                'kind' => 'Name',
                'value' => 'Link',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 5899,
            'end' => 5916,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5899,
              'end' => 5908,
            ),
            'kind' => 'Name',
            'value' => 'className',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5910,
              'end' => 5916,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5910,
                'end' => 5916,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 5919,
            'end' => 5942,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5919,
              'end' => 5934,
            ),
            'kind' => 'Name',
            'value' => 'numberOfElement',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5936,
              'end' => 5942,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5936,
                'end' => 5942,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        7 => 
        array (
          'loc' => 
          array (
            'start' => 5945,
            'end' => 5961,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5945,
              'end' => 5952,
            ),
            'kind' => 'Name',
            'value' => 'authors',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5954,
              'end' => 5961,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5955,
                'end' => 5960,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5955,
                  'end' => 5960,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        8 => 
        array (
          'loc' => 
          array (
            'start' => 5964,
            'end' => 5986,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5964,
              'end' => 5977,
            ),
            'kind' => 'Name',
            'value' => 'customColumns',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5979,
              'end' => 5986,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5980,
                'end' => 5985,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5980,
                  'end' => 5985,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        9 => 
        array (
          'loc' => 
          array (
            'start' => 5989,
            'end' => 6002,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5989,
              'end' => 5994,
            ),
            'kind' => 'Name',
            'value' => 'datas',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5996,
              'end' => 6002,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5997,
                'end' => 6001,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5997,
                  'end' => 6001,
                ),
                'kind' => 'Name',
                'value' => 'Data',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        10 => 
        array (
          'loc' => 
          array (
            'start' => 6005,
            'end' => 6021,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6005,
              'end' => 6012,
            ),
            'kind' => 'Name',
            'value' => 'formats',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6014,
              'end' => 6021,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6015,
                'end' => 6020,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6015,
                  'end' => 6020,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        11 => 
        array (
          'loc' => 
          array (
            'start' => 6024,
            'end' => 6044,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6024,
              'end' => 6035,
            ),
            'kind' => 'Name',
            'value' => 'identifiers',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6037,
              'end' => 6044,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6038,
                'end' => 6043,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6038,
                  'end' => 6043,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        12 => 
        array (
          'loc' => 
          array (
            'start' => 6047,
            'end' => 6064,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6047,
              'end' => 6056,
            ),
            'kind' => 'Name',
            'value' => 'languages',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6058,
              'end' => 6064,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6058,
                'end' => 6064,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        13 => 
        array (
          'loc' => 
          array (
            'start' => 6067,
            'end' => 6083,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6067,
              'end' => 6076,
            ),
            'kind' => 'Name',
            'value' => 'publisher',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6078,
              'end' => 6083,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6078,
                'end' => 6083,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        14 => 
        array (
          'loc' => 
          array (
            'start' => 6086,
            'end' => 6100,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6086,
              'end' => 6092,
            ),
            'kind' => 'Name',
            'value' => 'rating',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6094,
              'end' => 6100,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6094,
                'end' => 6100,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        15 => 
        array (
          'loc' => 
          array (
            'start' => 6103,
            'end' => 6115,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6103,
              'end' => 6108,
            ),
            'kind' => 'Name',
            'value' => 'serie',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6110,
              'end' => 6115,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6110,
                'end' => 6115,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        16 => 
        array (
          'loc' => 
          array (
            'start' => 6118,
            'end' => 6131,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6118,
              'end' => 6122,
            ),
            'kind' => 'Name',
            'value' => 'tags',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6124,
              'end' => 6131,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6125,
                'end' => 6130,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6125,
                  'end' => 6130,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        17 => 
        array (
          'loc' => 
          array (
            'start' => 6134,
            'end' => 6149,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6134,
              'end' => 6141,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6143,
              'end' => 6149,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6143,
                'end' => 6149,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        18 => 
        array (
          'loc' => 
          array (
            'start' => 6152,
            'end' => 6169,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6152,
              'end' => 6161,
            ),
            'kind' => 'Name',
            'value' => 'thumbnail',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6163,
              'end' => 6169,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6163,
                'end' => 6169,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        19 => 
        array (
          'loc' => 
          array (
            'start' => 6172,
            'end' => 6185,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6172,
              'end' => 6177,
            ),
            'kind' => 'Name',
            'value' => 'cover',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6179,
              'end' => 6185,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6179,
                'end' => 6185,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    5 => 
    array (
      'loc' => 
      array (
        'start' => 6189,
        'end' => 6264,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6194,
          'end' => 6198,
        ),
        'kind' => 'Name',
        'value' => 'Link',
      ),
      'interfaces' => 
      array (
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6203,
            'end' => 6216,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6203,
              'end' => 6207,
            ),
            'kind' => 'Name',
            'value' => 'href',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6209,
              'end' => 6216,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6209,
                'end' => 6215,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6209,
                  'end' => 6215,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6219,
            'end' => 6232,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6219,
              'end' => 6223,
            ),
            'kind' => 'Name',
            'value' => 'type',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6225,
              'end' => 6232,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6225,
                'end' => 6231,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6225,
                  'end' => 6231,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6235,
            'end' => 6246,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6235,
              'end' => 6238,
            ),
            'kind' => 'Name',
            'value' => 'rel',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6240,
              'end' => 6246,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6240,
                'end' => 6246,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6249,
            'end' => 6262,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6249,
              'end' => 6254,
            ),
            'kind' => 'Name',
            'value' => 'title',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6256,
              'end' => 6262,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6256,
                'end' => 6262,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    6 => 
    array (
      'loc' => 
      array (
        'start' => 6266,
        'end' => 6401,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6271,
          'end' => 6275,
        ),
        'kind' => 'Name',
        'value' => 'Data',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6287,
            'end' => 6291,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6287,
              'end' => 6291,
            ),
            'kind' => 'Name',
            'value' => 'Node',
          ),
        ),
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6296,
            'end' => 6303,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6296,
              'end' => 6298,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6300,
              'end' => 6303,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6300,
                'end' => 6302,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6300,
                  'end' => 6302,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6306,
            'end' => 6321,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6306,
              'end' => 6310,
            ),
            'kind' => 'Name',
            'value' => 'book',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6312,
              'end' => 6321,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6312,
                'end' => 6321,
              ),
              'kind' => 'Name',
              'value' => 'EntryBook',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6324,
            'end' => 6338,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6324,
              'end' => 6330,
            ),
            'kind' => 'Name',
            'value' => 'format',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6332,
              'end' => 6338,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6332,
                'end' => 6338,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6341,
            'end' => 6353,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6341,
              'end' => 6345,
            ),
            'kind' => 'Name',
            'value' => 'name',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6347,
              'end' => 6353,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6347,
                'end' => 6353,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 6356,
            'end' => 6365,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6356,
              'end' => 6360,
            ),
            'kind' => 'Name',
            'value' => 'size',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6362,
              'end' => 6365,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6362,
                'end' => 6365,
              ),
              'kind' => 'Name',
              'value' => 'Int',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 6368,
            'end' => 6381,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6368,
              'end' => 6373,
            ),
            'kind' => 'Name',
            'value' => 'mtime',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6375,
              'end' => 6381,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6375,
                'end' => 6381,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 6384,
            'end' => 6399,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6384,
              'end' => 6391,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6393,
              'end' => 6399,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6393,
                'end' => 6399,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    7 => 
    array (
      'loc' => 
      array (
        'start' => 6403,
        'end' => 6558,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6408,
          'end' => 6412,
        ),
        'kind' => 'Name',
        'value' => 'Note',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6424,
            'end' => 6428,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6424,
              'end' => 6428,
            ),
            'kind' => 'Name',
            'value' => 'Node',
          ),
        ),
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6433,
            'end' => 6440,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6433,
              'end' => 6435,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6437,
              'end' => 6440,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6437,
                'end' => 6439,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6437,
                  'end' => 6439,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6443,
            'end' => 6452,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6443,
              'end' => 6447,
            ),
            'kind' => 'Name',
            'value' => 'item',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6449,
              'end' => 6452,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6449,
                'end' => 6451,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6449,
                  'end' => 6451,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6455,
            'end' => 6468,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6455,
              'end' => 6459,
            ),
            'kind' => 'Name',
            'value' => 'type',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6461,
              'end' => 6468,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6461,
                'end' => 6467,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6461,
                  'end' => 6467,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6471,
            'end' => 6486,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6471,
              'end' => 6478,
            ),
            'kind' => 'Name',
            'value' => 'content',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6480,
              'end' => 6486,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6480,
                'end' => 6486,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 6489,
            'end' => 6498,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6489,
              'end' => 6493,
            ),
            'kind' => 'Name',
            'value' => 'size',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6495,
              'end' => 6498,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6495,
                'end' => 6498,
              ),
              'kind' => 'Name',
              'value' => 'Int',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 6501,
            'end' => 6514,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6501,
              'end' => 6506,
            ),
            'kind' => 'Name',
            'value' => 'mtime',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6508,
              'end' => 6514,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6508,
                'end' => 6514,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 6517,
            'end' => 6532,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6517,
              'end' => 6524,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6526,
              'end' => 6532,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6526,
                'end' => 6532,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        7 => 
        array (
          'loc' => 
          array (
            'start' => 6535,
            'end' => 6556,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6535,
              'end' => 6544,
            ),
            'kind' => 'Name',
            'value' => 'resources',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6546,
              'end' => 6556,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6547,
                'end' => 6555,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6547,
                  'end' => 6555,
                ),
                'kind' => 'Name',
                'value' => 'Resource',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    8 => 
    array (
      'loc' => 
      array (
        'start' => 6560,
        'end' => 6604,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6565,
          'end' => 6573,
        ),
        'kind' => 'Name',
        'value' => 'Resource',
      ),
      'interfaces' => 
      array (
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6578,
            'end' => 6587,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6578,
              'end' => 6582,
            ),
            'kind' => 'Name',
            'value' => 'hash',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6584,
              'end' => 6587,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6584,
                'end' => 6586,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6584,
                  'end' => 6586,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6590,
            'end' => 6602,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6590,
              'end' => 6594,
            ),
            'kind' => 'Name',
            'value' => 'name',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6596,
              'end' => 6602,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6596,
                'end' => 6602,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    9 => 
    array (
      'loc' => 
      array (
        'start' => 6606,
        'end' => 6644,
      ),
      'kind' => 'UnionTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6612,
          'end' => 6624,
        ),
        'kind' => 'Name',
        'value' => 'SearchResult',
      ),
      'directives' => 
      array (
      ),
      'types' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6627,
            'end' => 6632,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6627,
              'end' => 6632,
            ),
            'kind' => 'Name',
            'value' => 'Entry',
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6635,
            'end' => 6644,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6635,
              'end' => 6644,
            ),
            'kind' => 'Name',
            'value' => 'EntryBook',
          ),
        ),
      ),
    ),
  ),
);
